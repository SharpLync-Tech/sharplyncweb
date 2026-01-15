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

        $organisationName = (string) (DB::connection('sharpfleet')
            ->table('organisations')
            ->where('id', (int) $user['organisation_id'])
            ->value('name') ?? '');

        return view('sharpfleet.mobile.dashboard', compact(
            'user',
            'settingsService',
            'settings',
            'vehicles',
            'customers',
            'lastTrips',
            'activeTrip',
            'organisationName',
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

    public function bookings(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || Roles::normalize((string) $user['role']) !== Roles::DRIVER) {
            abort(403);
        }

        $organisationId = (int) $user['organisation_id'];
        $settingsService = new CompanySettingsService($organisationId);
        $companyTimezone = $settingsService->timezone();
        $settings = $settingsService->all();

        $branchesService = new BranchService();
        $branchesEnabled = $branchesService->branchesEnabled();
        $branches = $branchesEnabled ? $branchesService->getBranchesForUser($organisationId, (int) $user['id']) : collect();

        $branchAccessEnabled = $branchesEnabled
            && $branchesService->vehiclesHaveBranchSupport()
            && $branchesService->userBranchAccessEnabled();
        $accessibleBranchIds = $branchAccessEnabled
            ? $branchesService->getAccessibleBranchIdsForUser($organisationId, (int) $user['id'])
            : [];

        if ($branchAccessEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        $customersTableExists = Schema::connection('sharpfleet')->hasTable('customers');
        $customerEnabled = (bool) ($settings['customer']['enabled'] ?? false);
        $customerJoinEnabled = $customersTableExists && $customerEnabled;
        $customers = collect();

        if ($customersTableExists && $customerEnabled) {
            $customers = DB::connection('sharpfleet')
                ->table('customers')
                ->where('organisation_id', $organisationId)
                ->where('is_active', 1)
                ->when(
                    $branchAccessEnabled
                        && Schema::connection('sharpfleet')->hasColumn('customers', 'branch_id')
                        && count($accessibleBranchIds) > 0,
                    fn ($q) => $q->whereIn('branch_id', $accessibleBranchIds)
                )
                ->orderBy('name')
                ->limit(500)
                ->get();
        }

        $bookingsTableExists = Schema::connection('sharpfleet')->hasTable('bookings');
        $bookingsMine = collect();
        $bookingsOther = collect();

        $nowLocal = \Carbon\Carbon::now($companyTimezone);
        $dayStartLocal = $nowLocal->copy()->startOfDay();
        $dayEndLocal = $nowLocal->copy()->endOfDay();
        $weekStartLocal = $nowLocal->copy()->startOfWeek();
        $weekEndLocal = $nowLocal->copy()->endOfWeek();
        $monthStartLocal = $nowLocal->copy()->startOfMonth();
        $monthEndLocal = $nowLocal->copy()->endOfMonth();

        // Fetch a superset that covers the current month plus week edges.
        $fetchStartLocal = $monthStartLocal->copy()->startOfWeek();
        $fetchEndLocal = $monthEndLocal->copy()->endOfWeek();

        if ($bookingsTableExists) {
            $rangeStartUtc = $fetchStartLocal->copy()->timezone('UTC');
            $rangeEndUtc = $fetchEndLocal->copy()->timezone('UTC');

            $query = DB::connection('sharpfleet')
                ->table('bookings')
                ->join('vehicles', 'bookings.vehicle_id', '=', 'vehicles.id')
                ->when(
                    $customerJoinEnabled,
                    fn ($q) => $q->leftJoin('customers', 'bookings.customer_id', '=', 'customers.id')
                )
                ->select(
                    'bookings.id',
                    'bookings.user_id',
                    'bookings.planned_start',
                    'bookings.planned_end',
                    'bookings.status',
                    'bookings.timezone',
                    'vehicles.name as vehicle_name',
                    'vehicles.registration_number',
                    $customerJoinEnabled
                        ? DB::raw('COALESCE(customers.name, bookings.customer_name) as customer_name_display')
                        : DB::raw('bookings.customer_name as customer_name_display')
                )
                ->where('bookings.organisation_id', $organisationId)
                ->where('bookings.status', 'planned')
                ->where('bookings.planned_start', '<=', $rangeEndUtc->toDateTimeString())
                ->where('bookings.planned_end', '>=', $rangeStartUtc->toDateTimeString())
                ->when(
                    $branchAccessEnabled && $branchesService->bookingsHaveBranchSupport(),
                    fn ($q) => $q->whereIn('bookings.branch_id', $accessibleBranchIds)
                )
                ->when(
                    $branchAccessEnabled && !$branchesService->bookingsHaveBranchSupport() && $branchesService->vehiclesHaveBranchSupport(),
                    fn ($q) => $q->whereIn('vehicles.branch_id', $accessibleBranchIds)
                )
                ->orderBy('bookings.planned_start');

            $bookings = $query->get();

            $bookingsMine = $bookings->filter(function ($b) use ($user) {
                return (int) $b->user_id === (int) $user['id'];
            })->values();

            $bookingsOther = $bookings->filter(function ($b) use ($user) {
                return (int) $b->user_id !== (int) $user['id'];
            })->values();
        }

        $today = $nowLocal->format('Y-m-d');

        return view('sharpfleet.mobile.bookings', [
            'bookingsTableExists' => $bookingsTableExists,
            'bookingsMine' => $bookingsMine,
            'bookingsOther' => $bookingsOther,
            'branchesEnabled' => $branchesEnabled,
            'branches' => $branches,
            'customersTableExists' => $customersTableExists && $customerEnabled,
            'customers' => $customers,
            'companyTimezone' => $companyTimezone,
            'dayStartLocal' => $dayStartLocal,
            'dayEndLocal' => $dayEndLocal,
            'weekStartLocal' => $weekStartLocal,
            'weekEndLocal' => $weekEndLocal,
            'monthStartLocal' => $monthStartLocal,
            'monthEndLocal' => $monthEndLocal,
            'nowLocal' => $nowLocal,
            'today' => $today,
        ]);
    }

    public function more(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || Roles::normalize((string) $user['role']) !== Roles::DRIVER) {
            abort(403);
        }

        return view('sharpfleet.mobile.more');
    }

    public function help(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || Roles::normalize((string) $user['role']) !== Roles::DRIVER) {
            abort(403);
        }

        return view('sharpfleet.mobile.help');
    }

    public function about(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || Roles::normalize((string) $user['role']) !== Roles::DRIVER) {
            abort(403);
        }

        return view('sharpfleet.mobile.about');
    }
}
