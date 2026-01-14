<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\SharpFleet\CompanySettingsService;
use App\Services\SharpFleet\BranchService;
use App\Support\SharpFleet\Roles;

class DriverMobileController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || Roles::normalize((string) $user['role']) !== Roles::DRIVER) {
            abort(403);
        }

        $settingsService = new CompanySettingsService($user['organisation_id']);
        $settings = $settingsService->all();

        $allowPrivateTrips       = $settingsService->allowPrivateTrips();
        $faultsEnabled           = $settingsService->faultsEnabled();
        $allowFaultsDuringTrip   = $settingsService->allowFaultsDuringTrip();
        $companyTimezone         = $settingsService->timezone();

        $odometerRequired        = $settingsService->odometerRequired();
        $odometerAllowOverride   = $settingsService->odometerAllowOverride();
        $manualTripTimesRequired = $settingsService->requireManualStartEndTimes();

        $safetyCheckEnabled      = $settingsService->safetyCheckEnabled();
        $safetyCheckItems        = $settingsService->safetyCheckItems();

        $branchesService = new BranchService();

        $branchesEnabled = $branchesService->branchesEnabled();
        $branchAccessEnabled = $branchesEnabled
            && $branchesService->vehiclesHaveBranchSupport()
            && $branchesService->userBranchAccessEnabled();

        $accessibleBranchIds = $branchAccessEnabled
            ? $branchesService->getAccessibleBranchIdsForUser(
                (int) $user['organisation_id'],
                (int) $user['id']
            )
            : [];

        if ($branchAccessEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        $vehicles = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $user['organisation_id'])
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
                            $qq2->where('assignment_type', 'permanent')
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

        $customers = collect();
        if (($settings['customer']['enabled'] ?? false) && Schema::connection('sharpfleet')->hasTable('customers')) {
            $customers = DB::connection('sharpfleet')
                ->table('customers')
                ->where('organisation_id', $user['organisation_id'])
                ->where('is_active', 1)
                ->orderBy('name')
                ->limit(500)
                ->get();
        }

        $lastTrips = DB::connection('sharpfleet')
            ->table('trips')
            ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
            ->select('trips.vehicle_id', 'trips.end_km')
            ->where('trips.organisation_id', $user['organisation_id'])
            ->when(
                $branchAccessEnabled,
                fn ($q) => $q->whereIn('vehicles.branch_id', $accessibleBranchIds)
            )
            ->whereNotNull('ended_at')
            ->whereNotNull('end_km')
            ->orderByDesc('ended_at')
            ->get()
            ->unique('vehicle_id')
            ->keyBy('vehicle_id');

        $activeTrip = DB::connection('sharpfleet')
            ->table('trips')
            ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
            ->select(
                'trips.*',
                'vehicles.name as vehicle_name',
                'vehicles.registration_number',
                'vehicles.tracking_mode',
                'vehicles.branch_id as vehicle_branch_id'
            )
            ->where('trips.user_id', $user['id'])
            ->where('trips.organisation_id', $user['organisation_id'])
            ->when(
                $branchAccessEnabled,
                fn ($q) => $q->whereIn('vehicles.branch_id', $accessibleBranchIds)
            )
            ->whereNotNull('trips.started_at')
            ->whereNull('trips.ended_at')
            ->first();

        return view('sharpfleet.mobile.dashboard', compact(
            'settingsService',
            'settings',
            'vehicles',
            'customers',
            'lastTrips',
            'activeTrip',
            'allowPrivateTrips',
            'faultsEnabled',
            'allowFaultsDuringTrip',
            'companyTimezone',
            'odometerRequired',
            'odometerAllowOverride',
            'manualTripTimesRequired',
            'safetyCheckEnabled',
            'safetyCheckItems'
        ));
    }

    public function history(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || Roles::normalize((string) $user['role']) !== Roles::DRIVER) {
            abort(403);
        }

        return view('sharpfleet.mobile.history');
    }

    public function more(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || Roles::normalize((string) $user['role']) !== Roles::DRIVER) {
            abort(403);
        }

        return view('sharpfleet.mobile.more');
    }
}
