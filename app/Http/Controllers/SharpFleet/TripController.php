<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\TripService;
use App\Services\SharpFleet\CompanySettingsService;
use App\Services\SharpFleet\BranchService;
use App\Support\SharpFleet\Roles;
use App\Http\Requests\SharpFleet\Trips\StartTripRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;

class TripController extends Controller
{
    protected TripService $tripService;

    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

    /**
     * Start a trip (Driver UI – session based)
     */
    public function start(StartTripRequest $request): RedirectResponse
    {
        $user = session('sharpfleet.user');

        if (!$user) {
            abort(401, 'Not authenticated');
        }

        $this->tripService->startTrip(
            $user,
            $request->validated()
        );

        $previousUrl = url()->previous();
        $redirectTo = str_contains($previousUrl, '/app/sharpfleet/mobile')
            ? '/app/sharpfleet/mobile'
            : '/app/sharpfleet/driver';

        return redirect($redirectTo)
            ->with('success', 'Trip started successfully');
    }

    /**
     * End a trip (Driver UI – session based)
     */
    public function end(Request $request): RedirectResponse
    {
        $user = session('sharpfleet.user');

        if (!$user) {
            abort(401, 'Not authenticated');
        }

        $settings = new CompanySettingsService((int) $user['organisation_id']);
        $manualTimesRequired = $settings->requireManualStartEndTimes();

        $request->validate([
            'trip_id' => ['required', 'integer'],
            'end_km'  => ['required', 'integer', 'min:0'],
            'ended_at' => $manualTimesRequired ? ['required', 'date'] : ['nullable', 'date'],
        ]);

        $this->tripService->endTrip(
            $user,
            $request->only(['trip_id', 'end_km', 'ended_at'])
        );

        $previousUrl = url()->previous();
        $redirectTo = str_contains($previousUrl, '/app/sharpfleet/mobile')
            ? '/app/sharpfleet/mobile'
            : '/app/sharpfleet/driver';
        $separator = str_contains($redirectTo, '?') ? '&' : '?';
        $redirectTo .= $separator . 'refresh=' . time();

        return redirect($redirectTo)
            ->with('success', 'Trip ended successfully');
    }

    /**
     * Sync one or more completed offline trips (Driver UI – session based)
     */
    public function offlineSync(Request $request): JsonResponse
    {
        $user = session('sharpfleet.user');

        if (!$user) {
            abort(401, 'Not authenticated');
        }

        $validated = $request->validate([
            'trips' => ['required', 'array', 'min:1'],
            'trips.*.vehicle_id' => ['nullable', 'integer'],
            'trips.*.private_vehicle' => ['nullable', 'boolean'],
            'trips.*.trip_mode' => ['required', 'string'],
            'trips.*.start_km' => ['nullable', 'integer', 'min:0'],
            'trips.*.end_km' => ['required', 'integer', 'min:0'],
            'trips.*.started_at' => ['required', 'date'],
            'trips.*.ended_at' => ['required', 'date'],
            'trips.*.customer_id' => ['nullable', 'integer'],
            'trips.*.customer_name' => ['nullable', 'string', 'max:150'],
            'trips.*.client_present' => ['nullable'],
            'trips.*.client_address' => ['nullable', 'string'],
            'trips.*.purpose_of_travel' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $this->tripService->syncOfflineTrips($user, $validated['trips']);

        return response()->json($result);
    }

    /**
     * Fetch the latest ended-trip reading for a vehicle (Driver UI – session based).
     * Used to keep the PWA dashboard fresh without requiring a full page reload.
     */
    public function lastReading(Request $request): JsonResponse
    {
        $user = session('sharpfleet.user');

        if (!$user) {
            abort(401, 'Not authenticated');
        }

        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer'],
        ]);

        $vehicleId = (int) $validated['vehicle_id'];
        $organisationId = (int) $user['organisation_id'];

        // Enforce branch access (server-side).
        $branches = new BranchService();
        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($user);
        if (
            !$bypassBranchRestrictions
            && $branches->branchesEnabled()
            && $branches->vehiclesHaveBranchSupport()
            && $branches->userBranchAccessEnabled()
        ) {
            $vehicleBranchId = $branches->getBranchIdForVehicle($organisationId, $vehicleId);
            if ($vehicleBranchId && !$branches->userCanAccessBranch($organisationId, (int) $user['id'], (int) $vehicleBranchId)) {
                return response()->json([
                    'message' => 'Forbidden',
                ], 403);
            }
        }

        $vehicle = DB::connection('sharpfleet')
            ->table('vehicles')
            ->select('id', 'tracking_mode', 'starting_km')
            ->where('organisation_id', $organisationId)
            ->where('id', $vehicleId)
            ->first();

        if (!$vehicle) {
            return response()->json([
                'message' => 'Vehicle not found',
            ], 404);
        }

        $lastTrip = DB::connection('sharpfleet')
            ->table('trips')
            ->select('end_km', 'ended_at')
            ->where('organisation_id', $organisationId)
            ->where('vehicle_id', $vehicleId)
            ->whereNotNull('ended_at')
            ->whereNotNull('end_km')
            ->orderByDesc('ended_at')
            ->first();

        $fallbackStart = property_exists($vehicle, 'starting_km') ? ($vehicle->starting_km ?? null) : null;
        $lastKm = $lastTrip ? ($lastTrip->end_km ?? null) : $fallbackStart;

        return response()
            ->json([
                'vehicle_id' => $vehicleId,
                'tracking_mode' => $vehicle->tracking_mode ?? 'distance',
                'last_km' => $lastKm,
                'ended_at' => $lastTrip ? ($lastTrip->ended_at ?? null) : null,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Check if a vehicle has an active trip and return details for handover.
     */
    public function activeForVehicle(Request $request): JsonResponse
    {
        $user = session('sharpfleet.user');

        if (!$user) {
            abort(401, 'Not authenticated');
        }

        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer'],
        ]);

        $vehicleId = (int) $validated['vehicle_id'];
        $organisationId = (int) $user['organisation_id'];

        // Enforce branch access (server-side).
        $branches = new BranchService();
        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($user);
        if (
            !$bypassBranchRestrictions
            && $branches->branchesEnabled()
            && $branches->vehiclesHaveBranchSupport()
            && $branches->userBranchAccessEnabled()
        ) {
            $vehicleBranchId = $branches->getBranchIdForVehicle($organisationId, $vehicleId);
            if ($vehicleBranchId && !$branches->userCanAccessBranch($organisationId, (int) $user['id'], (int) $vehicleBranchId)) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
        }

        $trip = DB::connection('sharpfleet')
            ->table('trips')
            ->leftJoin('users', 'trips.user_id', '=', 'users.id')
            ->leftJoin('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
            ->select(
                'trips.id',
                'trips.user_id',
                'trips.started_at',
                'trips.start_km',
                'trips.timezone',
                'vehicles.name as vehicle_name',
                'vehicles.registration_number',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as driver_name")
            )
            ->where('trips.organisation_id', $organisationId)
            ->where('trips.vehicle_id', $vehicleId)
            ->whereNotNull('trips.started_at')
            ->whereNull('trips.ended_at')
            ->first();

        if (!$trip) {
            return response()->json(['active' => false]);
        }

        return response()->json([
            'active' => true,
            'trip' => [
                'trip_id' => (int) $trip->id,
                'driver_id' => (int) ($trip->user_id ?? 0),
                'driver_name' => (string) ($trip->driver_name ?? ''),
                'vehicle_name' => (string) ($trip->vehicle_name ?? ''),
                'registration_number' => (string) ($trip->registration_number ?? ''),
                'started_at' => $trip->started_at,
                'start_km' => $trip->start_km !== null ? (int) $trip->start_km : null,
                'timezone' => (string) ($trip->timezone ?? ''),
            ],
        ]);
    }

    /**
     * End a trip during handover (driver-assisted closure).
     */
    public function endHandover(Request $request): JsonResponse
    {
        $user = session('sharpfleet.user');

        if (!$user) {
            abort(401, 'Not authenticated');
        }

        $validated = $request->validate([
            'trip_id' => ['required', 'integer'],
            'end_km' => ['required', 'integer', 'min:0'],
            'confirm_takeover' => ['accepted'],
        ]);

        $organisationId = (int) $user['organisation_id'];
        $tripId = (int) $validated['trip_id'];
        $endKm = (int) $validated['end_km'];

        $trip = DB::connection('sharpfleet')
            ->table('trips')
            ->where('organisation_id', $organisationId)
            ->where('id', $tripId)
            ->whereNotNull('started_at')
            ->whereNull('ended_at')
            ->first();

        if (!$trip) {
            return response()->json(['message' => 'Trip not found'], 404);
        }

        if (!isset($trip->vehicle_id) || !$trip->vehicle_id) {
            return response()->json(['message' => 'Trip is not linked to a fleet vehicle'], 422);
        }

        // Enforce branch access (server-side) based on vehicle.
        $branches = new BranchService();
        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($user);
        if (
            !$bypassBranchRestrictions
            && $branches->branchesEnabled()
            && $branches->vehiclesHaveBranchSupport()
            && $branches->userBranchAccessEnabled()
        ) {
            $vehicleBranchId = $branches->getBranchIdForVehicle($organisationId, (int) $trip->vehicle_id);
            if ($vehicleBranchId && !$branches->userCanAccessBranch($organisationId, (int) $user['id'], (int) $vehicleBranchId)) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
        }

        if ($trip->start_km !== null && $endKm < (int) $trip->start_km) {
            return response()->json([
                'message' => 'Ending reading must be the same as or greater than the starting reading.',
            ], 422);
        }

        $now = now();
        $endedByOther = (int) ($trip->user_id ?? 0) !== (int) $user['id'];

        $update = [
            'end_km' => $endKm,
            'ended_at' => $now,
            'end_time' => $now,
        ];

        if (Schema::connection('sharpfleet')->hasColumn('trips', 'ended_by_other_driver')) {
            $update['ended_by_other_driver'] = $endedByOther ? 1 : 0;
        }
        if (Schema::connection('sharpfleet')->hasColumn('trips', 'ended_by_user_id')) {
            $update['ended_by_user_id'] = (int) $user['id'];
        }
        if (Schema::connection('sharpfleet')->hasColumn('trips', 'ended_reason')) {
            $update['ended_reason'] = 'handover';
        }

        DB::connection('sharpfleet')
            ->table('trips')
            ->where('id', $tripId)
            ->update($update);

        $vehicle = DB::connection('sharpfleet')
            ->table('vehicles')
            ->select('name', 'registration_number')
            ->where('organisation_id', $organisationId)
            ->where('id', (int) $trip->vehicle_id)
            ->first();

        $vehicleLabel = $vehicle
            ? trim((string) $vehicle->name . ' (' . (string) ($vehicle->registration_number ?? '') . ')')
            : 'Vehicle #' . (int) $trip->vehicle_id;

        $this->sendHandoverNotifications($organisationId, $trip, $user, $vehicleLabel, $endKm, $endedByOther);

        return response()->json([
            'ok' => true,
        ]);
    }

    private function sendHandoverNotifications(int $organisationId, object $trip, array $actingUser, string $vehicleLabel, int $endKm, bool $endedByOther): void
    {
        $previousDriver = null;
        if (Schema::connection('sharpfleet')->hasTable('users')) {
            $previousDriver = DB::connection('sharpfleet')
                ->table('users')
                ->select('id', 'email', 'first_name', 'last_name')
                ->where('id', (int) ($trip->user_id ?? 0))
                ->first();
        }

        $actorName = trim((string) (($actingUser['first_name'] ?? '') . ' ' . ($actingUser['last_name'] ?? '')));
        if ($actorName === '') {
            $actorName = 'A driver';
        }

        if ($endedByOther && $previousDriver && !empty($previousDriver->email)) {
            $body = implode("\n", [
                'Your trip was closed during a vehicle handover.',
                '',
                'Vehicle: ' . $vehicleLabel,
                'Closed by: ' . $actorName,
                'Closed at: ' . now()->toDateTimeString(),
                'Ending reading: ' . $endKm,
                '',
                'If this was unexpected, please contact your admin.',
            ]);

            try {
                Mail::raw($body, function ($message) use ($previousDriver, $vehicleLabel) {
                    $message->to((string) $previousDriver->email)
                        ->subject('SharpFleet: Trip closed during handover (' . $vehicleLabel . ')');
                });
            } catch (\Throwable $e) {
                // Ignore email failures for now
            }
        }

        $adminEmail = $this->resolveSubscriberAdminEmail($organisationId);
        if ($adminEmail) {
            $body = implode("\n", [
                'A trip was closed during a vehicle handover.',
                '',
                'Vehicle: ' . $vehicleLabel,
                'Closed by: ' . $actorName,
                'Original driver ID: ' . (int) ($trip->user_id ?? 0),
                'Closed at: ' . now()->toDateTimeString(),
                'Ending reading: ' . $endKm,
            ]);

            try {
                Mail::raw($body, function ($message) use ($adminEmail, $vehicleLabel) {
                    $message->to($adminEmail)
                        ->subject('SharpFleet: Trip closed during handover (' . $vehicleLabel . ')');
                });
            } catch (\Throwable $e) {
                // Ignore email failures for now
            }
        }
    }

    private function resolveSubscriberAdminEmail(int $organisationId): ?string
    {
        try {
            $orgColumns = Schema::connection('sharpfleet')->getColumnListing('organisations');
        } catch (\Throwable $e) {
            $orgColumns = [];
        }

        if (in_array('billing_email', $orgColumns, true)) {
            $billing = DB::connection('sharpfleet')
                ->table('organisations')
                ->where('id', $organisationId)
                ->value('billing_email');

            $billing = is_string($billing) ? trim($billing) : '';
            if ($billing !== '' && filter_var($billing, FILTER_VALIDATE_EMAIL)) {
                return $billing;
            }
        }

        if (!Schema::connection('sharpfleet')->hasTable('users')) {
            return null;
        }

        try {
            $userColumns = Schema::connection('sharpfleet')->getColumnListing('users');
        } catch (\Throwable $e) {
            $userColumns = [];
        }

        if (!in_array('organisation_id', $userColumns, true) || !in_array('email', $userColumns, true) || !in_array('role', $userColumns, true)) {
            return null;
        }

        $adminEmail = DB::connection('sharpfleet')
            ->table('users')
            ->where('organisation_id', $organisationId)
            ->where('role', 'admin')
            ->orderBy('id')
            ->value('email');

        $adminEmail = is_string($adminEmail) ? trim($adminEmail) : '';
        if ($adminEmail !== '' && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            return $adminEmail;
        }

        return null;
    }

    /**
     * Fetch available vehicles for driver start-trip UI (includes private vehicle option flag).
     */
    public function availableVehicles(Request $request): JsonResponse
    {
        $user = session('sharpfleet.user');

        if (!$user) {
            abort(401, 'Not authenticated');
        }

        $organisationId = (int) $user['organisation_id'];
        $settingsService = new CompanySettingsService($organisationId);

        $branchesService = new BranchService();
        $branchesEnabled = $branchesService->branchesEnabled();
        $branchAccessEnabled = $branchesEnabled
            && $branchesService->vehiclesHaveBranchSupport()
            && $branchesService->userBranchAccessEnabled();
        $accessibleBranchIds = $branchAccessEnabled
            ? $branchesService->getAccessibleBranchIdsForUser($organisationId, (int) $user['id'])
            : [];

        if ($branchAccessEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        $vehicles = DB::connection('sharpfleet')
            ->table('vehicles')
            ->select('id', 'name', 'registration_number', 'tracking_mode', 'branch_id')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->when(
                $branchAccessEnabled,
                fn ($q) => $q->whereIn('branch_id', $accessibleBranchIds)
            )
            ->when(
                Schema::connection('sharpfleet')->hasColumn('vehicles', 'assignment_type')
                && Schema::connection('sharpfleet')->hasColumn('vehicles', 'assigned_driver_id'),
                fn ($q) => $q->where(function ($qq) use ($user) {
                    $qq->whereNull('assignment_type')
                        ->orWhere('assignment_type', 'none')
                        ->orWhere(function ($qq2) use ($user) {
                            $qq2
                                ->where('assignment_type', 'permanent')
                                ->where('assigned_driver_id', (int) $user['id']);
                        });
                })
            )
            ->when(
                Schema::connection('sharpfleet')->hasColumn('vehicles', 'is_in_service'),
                fn ($q) => $q->where('is_in_service', 1)
            )
            ->orderBy('name')
            ->get();

        $vehicleIds = $vehicles->pluck('id')->map(fn ($id) => (int) $id)->all();

        $lastTrips = collect();
        if (count($vehicleIds) > 0) {
            $lastTrips = DB::connection('sharpfleet')
                ->table('trips')
                ->select('vehicle_id', 'end_km', 'ended_at')
                ->where('organisation_id', $organisationId)
                ->whereIn('vehicle_id', $vehicleIds)
                ->whereNotNull('ended_at')
                ->whereNotNull('end_km')
                ->orderByDesc('ended_at')
                ->get()
                ->unique('vehicle_id')
                ->keyBy('vehicle_id');
        }

        $availableVehicleCount = count($vehicleIds);
        if ($availableVehicleCount > 0) {
            $blockedVehicleIds = DB::connection('sharpfleet')
                ->table('trips')
                ->where('organisation_id', $organisationId)
                ->whereNotNull('started_at')
                ->whereNull('ended_at')
                ->whereNotNull('vehicle_id')
                ->whereIn('vehicle_id', $vehicleIds)
                ->pluck('vehicle_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (Schema::connection('sharpfleet')->hasTable('bookings')) {
                $nowUtc = \Carbon\Carbon::now('UTC');
                $bookingVehicleIds = DB::connection('sharpfleet')
                    ->table('bookings')
                    ->where('organisation_id', $organisationId)
                    ->whereIn('vehicle_id', $vehicleIds)
                    ->where('status', 'planned')
                    ->where('planned_start', '<=', $nowUtc->toDateTimeString())
                    ->where('planned_end', '>=', $nowUtc->toDateTimeString())
                    ->where('user_id', '!=', $user['id'])
                    ->pluck('vehicle_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $blockedVehicleIds = array_values(array_unique(array_merge($blockedVehicleIds, $bookingVehicleIds)));
            }

            $availableVehicleCount = count(array_diff($vehicleIds, $blockedVehicleIds));
        }

        $includePrivateVehicleOption = $settingsService->privateVehicleSlotsEnabled()
            && $availableVehicleCount === 0;

        $vehiclesPayload = $vehicles->map(function ($vehicle) use ($settingsService, $lastTrips) {
            $branchId = property_exists($vehicle, 'branch_id') ? (int) ($vehicle->branch_id ?? 0) : 0;
            $distanceUnit = $settingsService->distanceUnitForBranch($branchId > 0 ? $branchId : null);
            $lastKm = $lastTrips->get($vehicle->id)->end_km ?? null;

            return [
                'id' => (int) $vehicle->id,
                'name' => (string) ($vehicle->name ?? ''),
                'registration_number' => (string) ($vehicle->registration_number ?? ''),
                'tracking_mode' => (string) ($vehicle->tracking_mode ?? 'distance'),
                'distance_unit' => $distanceUnit,
                'last_km' => $lastKm !== null ? (string) $lastKm : '',
            ];
        })->values();

        return response()->json([
            'vehicles' => $vehiclesPayload,
            'available_vehicle_count' => $availableVehicleCount,
            'private_vehicle_option' => $includePrivateVehicleOption,
        ]);
    }

    public function edit($trip)
    {
        // to be implemented later
    }
}
