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

    public function edit($trip)
    {
        // to be implemented later
    }
}
