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

        // Enforce branch access restrictions for non-company-admins when the feature is enabled.
        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($user->toArray());
        if (
            !$bypassBranchRestrictions
            && $this->branchService->branchesEnabled()
            && $this->branchService->vehiclesHaveBranchSupport()
            && $this->branchService->userBranchAccessEnabled()
        ) {
            $ids = $this->branchService->getAccessibleBranchIdsForUser($organisationId, (int) $user->id);
            if (!empty($ids)) {
                $branchIds = $ids;
            } else {
                // If branch access is enabled and the user has no branches, they should see no vehicles.
                return response()->json([
                    'vehicles' => [],
                ]);
            }
        }

        $vehicles = $this->vehicleService->getAvailableVehicles($organisationId, $branchIds);

        // Match the mobile app expectation: a flat array of {id,label}.
        // Note: this project stores registration as registration_number (not rego).
        $payload = $vehicles->map(function ($v) {
            $id = (int) ($v->id ?? 0);

            $make = property_exists($v, 'make') ? trim((string) ($v->make ?? '')) : '';
            $model = property_exists($v, 'model') ? trim((string) ($v->model ?? '')) : '';

            $rego = '';
            if (property_exists($v, 'registration_number')) {
                $rego = trim((string) ($v->registration_number ?? ''));
            }

            $name = property_exists($v, 'name') ? trim((string) ($v->name ?? '')) : '';

            $labelLeft = trim($make . ' ' . $model);
            if ($labelLeft === '') {
                $labelLeft = $name !== '' ? $name : 'Vehicle';
            }

            $label = $rego !== '' ? ($labelLeft . ' – ' . $rego) : $labelLeft;

            return [
                'id' => $id,
                'label' => $label,
            ];
        })->values();

        return response()->json([
            'vehicles' => $payload,
        ]);
    }

    /**
     * Mobile API: get authoritative starting reading for a vehicle.
     *
     * Priority:
     *  1) Last completed trip end_km
     *  2) vehicles.starting_km
     */
    public function lastReading(Request $request, int $vehicle): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof SharpFleetUser) {
            abort(403, 'Invalid user context.');
        }

        $organisationId = (int) ($user->organisation_id ?? 0);
        if ($organisationId <= 0) {
            abort(403, 'No SharpFleet organisation context.');
        }

        // 1️⃣ Last completed trip for this vehicle
        $lastTrip = DB::connection('sharpfleet')
            ->table('trips')
            ->where('organisation_id', $organisationId)
            ->where('vehicle_id', $vehicle)
            ->whereNotNull('ended_at')
            ->whereNotNull('end_km')
            ->orderByDesc('ended_at')
            ->first();

        if ($lastTrip) {
            return response()->json([
                'vehicle_id' => $vehicle,
                'start_km'   => (int) $lastTrip->end_km,
                'source'     => 'last_trip',
            ]);
        }

        // 2️⃣ Fallback → vehicles.starting_km
        $v = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('id', $vehicle)
            ->first();

        return response()->json([
            'vehicle_id' => $vehicle,
            'start_km'   => isset($v->starting_km) ? (int) $v->starting_km : null,
            'source'     => 'vehicle_starting_km',
        ]);
    }
}
