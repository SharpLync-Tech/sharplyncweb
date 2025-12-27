<?php

namespace App\Services\SharpFleet;

use App\Models\SharpFleet\Trip;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class TripService
{
    protected CustomerService $customerService;
    protected BookingService $bookingService;

    public function __construct(CustomerService $customerService, BookingService $bookingService)
    {
        $this->customerService = $customerService;
        $this->bookingService = $bookingService;
    }

    private function normalizeTripMode(int $organisationId, ?string $tripMode): string
    {
        $raw = strtolower(trim((string) ($tripMode ?? '')));

        // Backwards compatible values from earlier UI.
        if ($raw === '' || $raw === 'client' || $raw === 'no_client' || $raw === 'internal') {
            $raw = 'business';
        }

        $settings = new CompanySettingsService($organisationId);
        $allowPrivate = $settings->allowPrivateTrips();

        if ($raw === 'private') {
            return $allowPrivate ? 'private' : 'business';
        }

        return $raw === 'business' ? 'business' : 'business';
    }

    /**
     * Start a trip for a SharpFleet driver
     */
    public function startTrip(array $user, array $data): Trip
    {
        $now = Carbon::now();

        $organisationId = (int) $user['organisation_id'];

        $tripMode = $this->normalizeTripMode($organisationId, $data['trip_mode'] ?? null);

        $this->bookingService->assertVehicleCanStartTrip(
            $organisationId,
            (int) $data['vehicle_id'],
            (int) $user['id'],
            $now
        );

        $customerId = null;
        $customerName = null;
        $clientPresent = null;
        $clientAddress = null;

        if ($tripMode !== 'private') {
            $customerId = isset($data['customer_id']) && $data['customer_id'] !== null && $data['customer_id'] !== ''
                ? (int) $data['customer_id']
                : null;

            $customerName = isset($data['customer_name'])
                ? trim((string) $data['customer_name'])
                : '';

            if ($customerName === '') {
                $customerName = null;
            }

            // If the driver selected from the admin list, store the canonical name.
            // If not found (or table not present), do not block trip start.
            if ($customerId) {
                $resolved = $this->customerService->getCustomerNameById($organisationId, $customerId);
                if ($resolved) {
                    $customerName = $resolved;
                } else {
                    $customerId = null;
                }
            }

            if ($customerName !== null && mb_strlen($customerName) > 150) {
                $customerName = mb_substr($customerName, 0, 150);
            }

            $clientPresent = $data['client_present'] ?? null;
            $clientAddress = $data['client_address'] ?? null;
        }

        $startKm = (int) $data['start_km'];

        // If there is a previous reading, do not allow starting below it.
        $lastTrip = Trip::where('vehicle_id', $data['vehicle_id'])
            ->where('organisation_id', $organisationId)
            ->whereNotNull('end_km')
            ->orderByDesc('ended_at')
            ->first();

        if ($lastTrip && $lastTrip->end_km !== null && $startKm < (int) $lastTrip->end_km) {
            throw ValidationException::withMessages([
                'start_km' => 'Starting reading must be the same as or greater than the last recorded reading.',
            ]);
        }

        return Trip::create([
            'organisation_id' => $organisationId,
            'user_id'         => $user['id'],
            'vehicle_id'      => $data['vehicle_id'],
            'customer_id'     => $customerId,
            'customer_name'   => $customerName,
            'trip_mode'       => $tripMode,
            'start_km'        => $startKm,
            'distance_method' => $data['distance_method'] ?? 'odometer',
            'client_present'  => $clientPresent,
            'client_address'  => $clientAddress,

            // Datetime fields (DB expects DATETIME, not TIME)
            'started_at' => $now,
            'start_time' => $now,
        ]);
    }

    /**
     * Sync completed trips captured offline by a driver.
     *
     * Expected $trips items include vehicle_id, trip_mode, start_km, end_km, started_at, ended_at.
     */
    public function syncOfflineTrips(array $user, array $trips): array
    {
        $organisationId = (int) ($user['organisation_id'] ?? 0);
        $userId = (int) ($user['id'] ?? 0);

        if ($organisationId <= 0 || $userId <= 0) {
            abort(401, 'Not authenticated');
        }

        $synced = [];
        $skipped = [];

        foreach ($trips as $t) {
            $vehicleId = (int) ($t['vehicle_id'] ?? 0);
            $tripMode = $this->normalizeTripMode($organisationId, isset($t['trip_mode']) ? (string) $t['trip_mode'] : null);
            $startKm = (int) ($t['start_km'] ?? 0);
            $endKm = (int) ($t['end_km'] ?? 0);

            $startedAt = Carbon::parse((string) ($t['started_at'] ?? ''));
            $endedAt = Carbon::parse((string) ($t['ended_at'] ?? ''));

            if ($vehicleId <= 0) {
                throw ValidationException::withMessages(['vehicle_id' => 'Vehicle is required.']);
            }
            if ($endedAt->lessThanOrEqualTo($startedAt)) {
                throw ValidationException::withMessages(['ended_at' => 'End time must be after start time.']);
            }
            if ($endKm < $startKm) {
                throw ValidationException::withMessages(['end_km' => 'Ending reading must be the same as or greater than the starting reading.']);
            }

            // De-dup: if we already have a trip with the exact same started_at for this driver/vehicle, skip it.
            $existing = Trip::where('organisation_id', $organisationId)
                ->where('user_id', $userId)
                ->where('vehicle_id', $vehicleId)
                ->where('started_at', $startedAt->toDateTimeString())
                ->first();

            if ($existing) {
                $skipped[] = (int) $existing->id;
                continue;
            }

            // Enforce booking lock at the time the trip started (best-effort).
            $this->bookingService->assertVehicleCanStartTrip($organisationId, $vehicleId, $userId, $startedAt);

            // Validate against last recorded reading for this vehicle.
            $lastTrip = Trip::where('vehicle_id', $vehicleId)
                ->where('organisation_id', $organisationId)
                ->whereNotNull('end_km')
                ->orderByDesc('ended_at')
                ->first();

            if ($lastTrip && $lastTrip->end_km !== null && $startKm < (int) $lastTrip->end_km) {
                throw ValidationException::withMessages([
                    'start_km' => 'Starting reading must be the same as or greater than the last recorded reading.',
                ]);
            }

            $customerId = null;
            $customerName = null;
            $clientPresent = null;
            $clientAddress = null;

            if ($tripMode !== 'private') {
                $customerId = isset($t['customer_id']) && $t['customer_id'] !== null && $t['customer_id'] !== ''
                    ? (int) $t['customer_id']
                    : null;

                $customerName = isset($t['customer_name']) ? trim((string) $t['customer_name']) : '';
                if ($customerName === '') {
                    $customerName = null;
                }
                if ($customerId) {
                    $resolved = $this->customerService->getCustomerNameById($organisationId, $customerId);
                    if ($resolved) {
                        $customerName = $resolved;
                    } else {
                        $customerId = null;
                    }
                }
                if ($customerName !== null && mb_strlen($customerName) > 150) {
                    $customerName = mb_substr($customerName, 0, 150);
                }

                $clientPresent = $t['client_present'] ?? null;
                $clientAddress = $t['client_address'] ?? null;
            }

            $trip = Trip::create([
                'organisation_id' => $organisationId,
                'user_id' => $userId,
                'vehicle_id' => $vehicleId,
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'trip_mode' => $tripMode,
                'start_km' => $startKm,
                'end_km' => $endKm,
                'distance_method' => 'odometer',
                'client_present' => $clientPresent,
                'client_address' => $clientAddress,
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'start_time' => $startedAt,
                'end_time' => $endedAt,
            ]);

            $synced[] = (int) $trip->id;
        }

        return [
            'synced' => $synced,
            'skipped' => $skipped,
        ];
    }

    /**
     * End an active trip
     */
    public function endTrip(array $user, array $data): Trip
    {
        $now = Carbon::now();

        $trip = Trip::where('id', $data['trip_id'])
            ->where('organisation_id', $user['organisation_id'])
            ->where('user_id', $user['id'])
            ->whereNull('ended_at')
            ->firstOrFail();

        $endKm = (int) $data['end_km'];
        $startKm = $trip->start_km !== null ? (int) $trip->start_km : null;

        if ($startKm !== null && $endKm < $startKm) {
            throw ValidationException::withMessages([
                'end_km' => 'Ending reading must be the same as or greater than the starting reading.',
            ]);
        }

        $trip->update([
            'end_km'   => $endKm,
            'ended_at' => $now,
            'end_time' => $now,
        ]);

        return $trip;
    }

    public function editTrip()
    {
        // to be implemented
    }
}
