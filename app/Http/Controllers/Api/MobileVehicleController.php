<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SharpFleet\User as SharpFleetUser;
use App\Services\SharpFleet\BranchService;
use App\Services\SharpFleet\VehicleService;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileVehicleController extends Controller
{
    public function __construct(
        private VehicleService $vehicleService,
        private BranchService $branchService,
    ) {
    }

    /**
     * Mobile API: list vehicles the authenticated user can access.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof SharpFleetUser) {
            abort(403, 'Invalid user context.');
        }

        $organisationId = (int) ($user->organisation_id ?? 0);
        if ($organisationId <= 0) {
            abort(403, 'No SharpFleet organisation context.');
        }

        $branchIds = null;

        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($user->toArray());
        if (
            !$bypassBranchRestrictions
            && $this->branchService->branchesEnabled()
            && $this->branchService->vehiclesHaveBranchSupport()
            && $this->branchService->userBranchAccessEnabled()
        ) {
            $ids = $this->branchService->getAccessibleBranchIdsForUser(
                $organisationId,
                (int) $user->id
            );

            if (!empty($ids)) {
                $branchIds = $ids;
            } else {
                return response()->json(['vehicles' => []]);
            }
        }

        $vehicles = $this->vehicleService->getAvailableVehicles($organisationId, $branchIds);

        $payload = $vehicles->map(function ($v) {
            $id = (int) ($v->id ?? 0);

            $make  = trim((string) ($v->make ?? ''));
            $model = trim((string) ($v->model ?? ''));
            $rego  = trim((string) ($v->registration_number ?? ''));
            $name  = trim((string) ($v->name ?? ''));

            $labelLeft = trim($make . ' ' . $model);
            if ($labelLeft === '') {
                $labelLeft = $name !== '' ? $name : 'Vehicle';
            }

            $label = $rego !== '' ? ($labelLeft . ' – ' . $rego) : $labelLeft;

            return [
                'id'    => $id,
                'label' => $label,
            ];
        })->values();

        return response()->json(['vehicles' => $payload]);
    }

    /**
     * Mobile API: get last reading for a vehicle
     *
     * Order:
     * 1️⃣ Last completed trip (ended_at + end_km)
     * 2️⃣ vehicles.starting_km
     * 3️⃣ null (manual entry)
     */
    public function lastReading(Request $request, int $vehicleId): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof SharpFleetUser) {
            abort(403, 'Invalid user context.');
        }

        $organisationId = (int) ($user->organisation_id ?? 0);
        if ($organisationId <= 0) {
            abort(403, 'No SharpFleet organisation context.');
        }

        $vehicle = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('id', $vehicleId)
            ->where('organisation_id', $organisationId)
            ->first();

        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        $trackingMode = $vehicle->tracking_mode ?? 'distance';

        // tracking_mode = none → manual only
        if ($trackingMode === 'none') {
            return response()->json([
                'vehicle_id'    => $vehicleId,
                'tracking_mode' => 'none',
                'last_reading'  => null,
                'source'        => null,
            ]);
        }

        // 1️⃣ Last completed trip
        $lastTrip = DB::connection('sharpfleet')
            ->table('trips')
            ->where('vehicle_id', $vehicleId)
            ->where('organisation_id', $organisationId)
            ->whereNotNull('ended_at')
            ->whereNotNull('end_km')
            ->orderByDesc('ended_at')
            ->first();

        if ($lastTrip) {
            return response()->json([
                'vehicle_id'    => $vehicleId,
                'tracking_mode' => $trackingMode,
                'last_reading'  => (int) $lastTrip->end_km,
                'source'        => 'last_trip',
            ]);
        }

        // 2️⃣ Fallback to vehicles.starting_km
        if ($vehicle->starting_km !== null) {
            return response()->json([
                'vehicle_id'    => $vehicleId,
                'tracking_mode' => $trackingMode,
                'last_reading'  => (int) $vehicle->starting_km,
                'source'        => 'vehicle_starting_km',
            ]);
        }

        // 3️⃣ Manual entry required
        return response()->json([
            'vehicle_id'    => $vehicleId,
            'tracking_mode' => $trackingMode,
            'last_reading'  => null,
            'source'        => null,
        ]);
    }
}
