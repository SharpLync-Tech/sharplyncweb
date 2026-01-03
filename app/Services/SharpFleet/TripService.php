<?php

namespace App\Services\SharpFleet;

use App\Models\SharpFleet\Trip;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TripService
{
    protected CustomerService $customerService;
    protected BookingService $bookingService;

    /** @var array<int, string>|null */
    private static ?array $tripModeAllowedValues = null;

    public function __construct(CustomerService $customerService, BookingService $bookingService)
    {
        $this->customerService = $customerService;
        $this->bookingService = $bookingService;
    }

    private function parseDriverLocalDateTime(?string $raw, string $companyTimezone): ?Carbon
    {
        $value = trim((string) ($raw ?? ''));
        if ($value === '') {
            return null;
        }

        $appTz = (string) (config('app.timezone') ?: 'UTC');

        try {
            $dt = Carbon::createFromFormat('Y-m-d\\TH:i', $value, $companyTimezone);
            return $dt ? $dt->setTimezone($appTz) : null;
        } catch (\Throwable $e) {
            // fall through
        }

        try {
            $dt = Carbon::createFromFormat('Y-m-d\\TH:i:s', $value, $companyTimezone);
            return $dt ? $dt->setTimezone($appTz) : null;
        } catch (\Throwable $e) {
            // fall through
        }

        try {
            return Carbon::parse($value, $companyTimezone)->setTimezone($appTz);
        } catch (\Throwable $e) {
            return null;
        }
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
     * Resolve the trip_mode value to store in the DB, supporting legacy schemas.
     *
     * Some tenants have trip_mode as an ENUM without 'business' (e.g. client/no_client/internal/private).
     */
    private function tripModeForStorage(string $canonicalTripMode, array $data): string
    {
        $canonical = strtolower(trim($canonicalTripMode));
        if ($canonical === 'private') {
            return $this->preferredAllowedTripMode(['private']);
        }

        // Canonical business trip.
        $allowed = $this->getTripModeAllowedValues();
        if (is_array($allowed) && in_array('business', $allowed, true)) {
            return 'business';
        }

        // Legacy schemas: prefer client/no_client/internal.
        $clientPresent = $data['client_present'] ?? null;
        if ($clientPresent === 1 || $clientPresent === '1' || $clientPresent === true) {
            return $this->preferredAllowedTripMode(['client', 'business', 'internal', 'no_client']);
        }
        if ($clientPresent === 0 || $clientPresent === '0' || $clientPresent === false) {
            return $this->preferredAllowedTripMode(['no_client', 'business', 'internal', 'client']);
        }

        return $this->preferredAllowedTripMode(['business', 'internal', 'no_client', 'client']);
    }

    /** @return array<int, string>|null */
    private function getTripModeAllowedValues(): ?array
    {
        if (self::$tripModeAllowedValues !== null) {
            return self::$tripModeAllowedValues;
        }

        try {
            $row = DB::connection('sharpfleet')->selectOne("SHOW COLUMNS FROM trips WHERE Field = 'trip_mode'");
            if (!$row || !isset($row->Type) || !is_string($row->Type)) {
                return self::$tripModeAllowedValues = null;
            }

            $type = strtolower($row->Type);
            if (!str_starts_with($type, 'enum(')) {
                return self::$tripModeAllowedValues = null;
            }

            // enum('a','b','c') -> ['a','b','c']
            if (!preg_match_all("/'([^']*)'/", $row->Type, $m)) {
                return self::$tripModeAllowedValues = null;
            }

            $vals = array_values(array_filter(array_map('strval', $m[1]), fn ($v) => $v !== ''));
            return self::$tripModeAllowedValues = $vals;
        } catch (\Throwable $e) {
            return self::$tripModeAllowedValues = null;
        }
    }

    /**
     * Pick the first preferred value that exists in the column's enum; if we can't detect enum values,
     * just return the first preferred.
     *
     * @param array<int, string> $preferred
     */
    private function preferredAllowedTripMode(array $preferred): string
    {
        $allowed = $this->getTripModeAllowedValues();
        if (!is_array($allowed)) {
            return (string) ($preferred[0] ?? 'business');
        }

        foreach ($preferred as $candidate) {
            if (in_array($candidate, $allowed, true)) {
                return $candidate;
            }
        }

        // Fall back to the first allowed enum value if nothing matched.
        return (string) ($allowed[0] ?? (string) ($preferred[0] ?? 'business'));
    }

    /**
     * Start a trip for a SharpFleet driver
     */
    public function startTrip(array $user, array $data): Trip
    {
        $organisationId = (int) $user['organisation_id'];

        $settings = new CompanySettingsService($organisationId);

        $now = Carbon::now();
        if ($settings->requireManualStartEndTimes()) {
            $manualStartedAt = $this->parseDriverLocalDateTime($data['started_at'] ?? null, $settings->timezone());
            if ($manualStartedAt) {
                $now = $manualStartedAt;
            }
        }

        $tripMode = $this->normalizeTripMode($organisationId, $data['trip_mode'] ?? null);

        // Permanent vehicle assignment rules (booking is not required for the assigned driver).
        $this->bookingService->assertVehicleAssignmentAllowsTrip(
            $organisationId,
            (int) $data['vehicle_id'],
            (int) $user['id']
        );

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
        $purposeOfTravel = null;

        $customerCaptureEnabled = (bool) (($settings->all()['customer']['enabled'] ?? false));
        $clientPresenceEnabled = $settings->clientPresenceEnabled();
        $clientPresenceRequired = $settings->clientPresenceRequired();
        $clientAddressesEnabled = $settings->clientAddressesEnabled();
        $purposeOfTravelEnabled = $settings->purposeOfTravelEnabled();

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

            if (!$customerCaptureEnabled) {
                $customerId = null;
                $customerName = null;
            }

            if ($clientPresenceEnabled) {
                $clientPresent = $data['client_present'] ?? null;

                if ($clientPresenceRequired && ($clientPresent === null || $clientPresent === '')) {
                    throw ValidationException::withMessages([
                        'client_present' => 'Client presence is required to start a business trip.',
                    ]);
                }

                $clientAddress = $clientAddressesEnabled ? ($data['client_address'] ?? null) : null;
            } else {
                $clientPresent = null;
                $clientAddress = null;
            }

            if ($purposeOfTravelEnabled) {
                $purposeOfTravel = isset($data['purpose_of_travel']) ? trim((string) $data['purpose_of_travel']) : '';
                if ($purposeOfTravel === '') {
                    $purposeOfTravel = null;
                } elseif (mb_strlen($purposeOfTravel) > 255) {
                    $purposeOfTravel = mb_substr($purposeOfTravel, 0, 255);
                }
            }
        }

        $startKmRaw = $data['start_km'] ?? null;
        $startKm = $startKmRaw === null || $startKmRaw === '' ? null : (int) $startKmRaw;

        // If there is a previous reading, do not allow starting below it.
        $lastTrip = Trip::where('vehicle_id', $data['vehicle_id'])
            ->where('organisation_id', $organisationId)
            ->whereNotNull('end_km')
            ->orderByDesc('ended_at')
            ->first();

        $vehicle = DB::connection('sharpfleet')
            ->table('vehicles')
            ->select('starting_km')
            ->where('organisation_id', $organisationId)
            ->where('id', (int) $data['vehicle_id'])
            ->first();

        $baselineReading = null;
        if ($lastTrip && $lastTrip->end_km !== null) {
            $baselineReading = (int) $lastTrip->end_km;
        } elseif ($vehicle && property_exists($vehicle, 'starting_km') && $vehicle->starting_km !== null) {
            $baselineReading = (int) $vehicle->starting_km;
        }

        // If odometer is not required, allow empty start_km but autofill from baseline when possible.
        if ($startKm === null) {
            if ($settings->odometerRequired()) {
                throw ValidationException::withMessages([
                    'start_km' => 'Starting reading is required.',
                ]);
            }

            if ($settings->odometerAutofillEnabled() && $baselineReading !== null) {
                $startKm = $baselineReading;
            } else {
                throw ValidationException::withMessages([
                    'start_km' => 'Starting reading is required for this vehicle because no previous reading is available.',
                ]);
            }
        }

        // If override is disabled and we know the baseline, enforce equality.
        if (!$settings->odometerAllowOverride() && $baselineReading !== null && $startKm !== $baselineReading) {
            throw ValidationException::withMessages([
                'start_km' => 'Starting reading cannot be changed. It must match the last recorded reading.',
            ]);
        }

        if ($baselineReading !== null && $startKm < (int) $baselineReading) {
            throw ValidationException::withMessages([
                'start_km' => 'Starting reading must be the same as or greater than the last recorded reading.',
            ]);
        }

        $tripModeToStore = $this->tripModeForStorage($tripMode, $data);

        return Trip::create([
            'organisation_id' => $organisationId,
            'user_id'         => $user['id'],
            'vehicle_id'      => $data['vehicle_id'],
            'customer_id'     => $customerId,
            'customer_name'   => $customerName,
            'trip_mode'       => $tripModeToStore,
            'start_km'        => $startKm,
            'distance_method' => $data['distance_method'] ?? 'odometer',
            'client_present'  => $clientPresent,
            'client_address'  => $clientAddress,
            'purpose_of_travel' => $purposeOfTravel,

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
            $purposeOfTravel = null;

            $settings = new CompanySettingsService($organisationId);
            $purposeOfTravelEnabled = $settings->purposeOfTravelEnabled();

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

                if ($purposeOfTravelEnabled) {
                    $purposeOfTravel = isset($t['purpose_of_travel']) ? trim((string) $t['purpose_of_travel']) : '';
                    if ($purposeOfTravel === '') {
                        $purposeOfTravel = null;
                    } elseif (mb_strlen($purposeOfTravel) > 255) {
                        $purposeOfTravel = mb_substr($purposeOfTravel, 0, 255);
                    }
                }
            }

            $tripModeToStore = $this->tripModeForStorage($tripMode, [
                'client_present' => $clientPresent,
            ]);

            $trip = Trip::create([
                'organisation_id' => $organisationId,
                'user_id' => $userId,
                'vehicle_id' => $vehicleId,
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'trip_mode' => $tripModeToStore,
                'start_km' => $startKm,
                'end_km' => $endKm,
                'distance_method' => 'odometer',
                'client_present' => $clientPresent,
                'client_address' => $clientAddress,
                'purpose_of_travel' => $purposeOfTravel,
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

        $settings = new CompanySettingsService((int) $user['organisation_id']);
        if ($settings->requireManualStartEndTimes()) {
            $manualEndedAt = $this->parseDriverLocalDateTime($data['ended_at'] ?? null, $settings->timezone());
            if ($manualEndedAt) {
                $now = $manualEndedAt;
            }
        }

        $endKm = (int) $data['end_km'];
        $startKm = $trip->start_km !== null ? (int) $trip->start_km : null;

        if ($startKm !== null && $endKm < $startKm) {
            throw ValidationException::withMessages([
                'end_km' => 'Ending reading must be the same as or greater than the starting reading.',
            ]);
        }

        if ($trip->started_at) {
            $startedAt = Carbon::parse((string) $trip->started_at);
            if ($now->lessThanOrEqualTo($startedAt)) {
                throw ValidationException::withMessages([
                    'ended_at' => 'End time must be after start time.',
                ]);
            }
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
