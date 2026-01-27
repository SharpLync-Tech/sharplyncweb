<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BookingService;
use App\Services\SharpFleet\BranchService;
use App\Services\SharpFleet\CompanySettingsService;
use App\Support\SharpFleet\Roles;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    private function branchAccessContext(array $user): array
    {
        $organisationId = (int) ($user['organisation_id'] ?? 0);

        $branchesService = new BranchService();
        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($user);
        $branchesEnabled = $branchesService->branchesEnabled();
        $branchAccessEnabled = $branchesEnabled
            && $branchesService->vehiclesHaveBranchSupport()
            && $branchesService->userBranchAccessEnabled();
        $branchScopeEnabled = $branchAccessEnabled && !$bypassBranchRestrictions;

        $accessibleBranchIds = $branchScopeEnabled
            ? $branchesService->getAccessibleBranchIdsForUser($organisationId, (int) ($user['id'] ?? 0))
            : [];
        $accessibleBranchIds = array_values(array_unique(array_filter(array_map('intval', $accessibleBranchIds), fn ($v) => $v > 0)));
        if ($branchScopeEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        return [
            'branchesService' => $branchesService,
            'branchScopeEnabled' => $branchScopeEnabled,
            'accessibleBranchIds' => $accessibleBranchIds,
        ];
    }

    private function assertBranchAccessible(?int $branchId, array $ctx): void
    {
        if (!$ctx['branchScopeEnabled']) {
            return;
        }

        if (!$branchId || $branchId <= 0) {
            return;
        }

        if (!in_array((int) $branchId, $ctx['accessibleBranchIds'], true)) {
            abort(403, 'No branch access.');
        }
    }

    private function assertVehicleAccessible(int $organisationId, int $vehicleId, array $ctx): void
    {
        if (!$ctx['branchScopeEnabled']) {
            return;
        }

        /** @var BranchService $branchesService */
        $branchesService = $ctx['branchesService'];
        if (!$branchesService->vehiclesHaveBranchSupport()) {
            return;
        }

        $vehicleBranchId = $branchesService->getBranchIdForVehicle($organisationId, $vehicleId);
        if ($vehicleBranchId && !in_array((int) $vehicleBranchId, $ctx['accessibleBranchIds'], true)) {
            abort(403, 'No branch access.');
        }
    }

    private function assertBookingAccessible(int $organisationId, int $bookingId, array $ctx): void
    {
        if (!$ctx['branchScopeEnabled']) {
            return;
        }

        /** @var BranchService $branchesService */
        $branchesService = $ctx['branchesService'];
        $hasBookings = Schema::connection('sharpfleet')->hasTable('bookings');
        if (!$hasBookings) {
            return;
        }

        $hasBookingBranch = Schema::connection('sharpfleet')->hasColumn('bookings', 'branch_id');
        $hasVehicleBranch = $branchesService->vehiclesHaveBranchSupport() && Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id');
        if (!$hasBookingBranch && !$hasVehicleBranch) {
            return;
        }

        $query = DB::connection('sharpfleet')
            ->table('bookings')
            ->where('bookings.organisation_id', $organisationId)
            ->where('bookings.id', $bookingId);

        if ($hasVehicleBranch) {
            $query->leftJoin('vehicles', 'bookings.vehicle_id', '=', 'vehicles.id');
        }

        $row = $query->select(
            $hasBookingBranch ? 'bookings.branch_id as booking_branch_id' : DB::raw('NULL as booking_branch_id'),
            $hasVehicleBranch ? 'vehicles.branch_id as vehicle_branch_id' : DB::raw('NULL as vehicle_branch_id')
        )->first();

        if (!$row) {
            return;
        }

        $effectiveBranchId = $hasBookingBranch ? (int) ($row->booking_branch_id ?? 0) : 0;
        if ($effectiveBranchId <= 0 && $hasVehicleBranch) {
            $effectiveBranchId = (int) ($row->vehicle_branch_id ?? 0);
        }

        if ($effectiveBranchId > 0 && !in_array($effectiveBranchId, $ctx['accessibleBranchIds'], true)) {
            abort(403, 'No branch access.');
        }
    }

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');
        if (!$user) {
            abort(403);
        }

        $organisationId = (int) $user['organisation_id'];

        $branchesService = new BranchService();
        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($user);
        $branchesEnabled = $branchesService->branchesEnabled();
        $branchAccessEnabled = $branchesEnabled
            && $branchesService->vehiclesHaveBranchSupport()
            && $branchesService->userBranchAccessEnabled();
        // Company admins bypass branch scoping entirely.
        $branchScopeEnabled = $branchAccessEnabled && !$bypassBranchRestrictions;
        $accessibleBranchIds = $branchScopeEnabled
            ? $branchesService->getAccessibleBranchIdsForUser($organisationId, (int) $user['id'])
            : [];
        if ($branchScopeEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        // Fallback to users.branch_id when user_branch_access is not available.
        if (!$branchScopeEnabled && !$bypassBranchRestrictions && $branchesEnabled && Schema::connection('sharpfleet')->hasColumn('users', 'branch_id')) {
            $fallbackBranchId = $branchesService->getPrimaryBranchIdForUser($organisationId, (int) $user['id']);
            if ($fallbackBranchId) {
                $branchScopeEnabled = true;
                $accessibleBranchIds = [(int) $fallbackBranchId];
            }
        }

        $branches = $branchesEnabled
            ? ($branchScopeEnabled ? $branchesService->getBranchesForUser($organisationId, (int) $user['id']) : $branchesService->getBranches($organisationId))
            : collect();
        $defaultBranch = $branchesEnabled ? $branches->first() : null;
        $defaultTimezone = $defaultBranch && isset($defaultBranch->timezone) && trim((string) $defaultBranch->timezone) !== ''
            ? (string) $defaultBranch->timezone
            : (new CompanySettingsService($organisationId))->timezone();

        $vehicles = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->when(
                $branchScopeEnabled && Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id'),
                fn ($q) => $q->whereIn('branch_id', $accessibleBranchIds)
            )
            ->when(
                Schema::connection('sharpfleet')->hasColumn('vehicles', 'assignment_type'),
                fn ($q) => $q->where(function ($qq) {
                    $qq->whereNull('assignment_type')
                        ->orWhere('assignment_type', 'none');
                })
            )
            ->when(
                Schema::connection('sharpfleet')->hasColumn('vehicles', 'is_in_service'),
                fn ($q) => $q->where('is_in_service', 1)
            )
            ->orderBy('name')
            ->get();

        $role = Roles::normalize($user['role'] ?? null);

        $drivers = DB::connection('sharpfleet')
            ->table('users')
            ->when($branchScopeEnabled && $branchAccessEnabled, function ($q) use ($organisationId, $accessibleBranchIds) {
                $q->join('user_branch_access as uba', function ($join) use ($organisationId) {
                    $join->on('users.id', '=', 'uba.user_id')
                        ->where('uba.organisation_id', '=', $organisationId);
                });
                if (Schema::connection('sharpfleet')->hasColumn('user_branch_access', 'is_active')) {
                    $q->where('uba.is_active', 1);
                }
                $q->whereIn('uba.branch_id', $accessibleBranchIds);
            })
            ->when($branchScopeEnabled && !$branchAccessEnabled && Schema::connection('sharpfleet')->hasColumn('users', 'branch_id'), function ($q) use ($accessibleBranchIds) {
                $q->whereIn('users.branch_id', $accessibleBranchIds);
            })
            ->where('users.organisation_id', $organisationId)
            ->when($role !== Roles::COMPANY_ADMIN, function ($q) {
                $q->whereNotIn('users.role', [Roles::COMPANY_ADMIN, 'admin']);
            })
            ->where(function ($q) {
                $q
                    ->where(function ($qq) {
                        $qq
                            ->where('role', 'driver')
                            ->where(function ($q2) {
                                $q2->whereNull('is_driver')->orWhere('is_driver', 1);
                            });
                    })
                    ->orWhere(function ($qq) {
                        $qq->where('is_driver', 1);
                    });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $customersTableExists = Schema::connection('sharpfleet')->hasTable('customers');
        $customers = collect();
        if ($customersTableExists) {
            $customers = DB::connection('sharpfleet')
                ->table('customers')
                ->where('organisation_id', $organisationId)
                ->where('is_active', 1)
                ->when($branchScopeEnabled && Schema::connection('sharpfleet')->hasColumn('customers', 'branch_id'), function ($q) use ($accessibleBranchIds) {
                    $q->whereIn('branch_id', $accessibleBranchIds);
                })
                ->orderBy('name')
                ->limit(500)
                ->get();
        }

        $result = $this->bookingService->getUpcomingBookings($organisationId, $user);

        return view('sharpfleet.admin.bookings.index', [
            'bookingsTableExists' => $result['tableExists'],
            'bookings' => $result['bookings'],
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'customersTableExists' => $customersTableExists,
            'customers' => $customers,
            'branchesEnabled' => $branchesEnabled,
            'branches' => $branches,
            'defaultTimezone' => $defaultTimezone,
        ]);
    }

    public function feed(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');
        if (!$user) {
            abort(403);
        }

        $validated = $request->validate([
            'start' => ['required', 'date'],
            // Day view requests a single-day range (start=end).
            'end' => ['required', 'date', 'after_or_equal:start'],
            'branch_id' => ['nullable', 'integer'],
        ]);

        $organisationId = (int) $user['organisation_id'];

        $branchesService = new BranchService();
        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($user);
        $branchesEnabled = $branchesService->branchesEnabled();
        $branchAccessEnabled = $branchesEnabled
            && $branchesService->vehiclesHaveBranchSupport()
            && $branchesService->userBranchAccessEnabled();
        $branchScopeEnabled = $branchAccessEnabled && !$bypassBranchRestrictions;
        $accessibleBranchIds = $branchScopeEnabled
            ? $branchesService->getAccessibleBranchIdsForUser($organisationId, (int) $user['id'])
            : [];
        if ($branchScopeEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        $timezone = (string) ($request->query('tz') ?: ($request->query('timezone') ?: ''));
        if (trim($timezone) === '') {
            $timezone = (new CompanySettingsService($organisationId))->timezone();
        }

        $rangeStart = Carbon::parse($validated['start'], $timezone)->startOfDay();
        $rangeEnd = Carbon::parse($validated['end'], $timezone)->endOfDay();

        $branchIdFilter = isset($validated['branch_id']) && $validated['branch_id'] !== null && $validated['branch_id'] !== ''
            ? (int) $validated['branch_id']
            : null;

        $bookings = $this->bookingService->getBookingsInRange(
            organisationId: $organisationId,
            rangeStartLocal: $rangeStart,
            rangeEndLocal: $rangeEnd,
            actor: $user,
            bypassBranchRestrictions: $bypassBranchRestrictions,
            branchIdFilter: $branchIdFilter
        );

        return response()->json([
            'timezone' => $timezone,
            'bookings' => $bookings,
        ]);
    }

    /**
     * List current active trips (started but not ended)
     */
    public function activeTrips(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');
        if (!$user) {
            abort(403);
        }

        $organisationId = (int) ($user['organisation_id'] ?? 0);
        if ($organisationId <= 0) {
            abort(403, 'No SharpFleet organisation context.');
        }

        $ctx = $this->branchAccessContext($user);
        $tripsTableExists = Schema::connection('sharpfleet')->hasTable('trips');

        $trips = collect();
        if ($tripsTableExists) {
            $trips = DB::connection('sharpfleet')
                ->table('trips')
                ->leftJoin('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
                ->leftJoin('users', 'trips.user_id', '=', 'users.id')
                ->where('trips.organisation_id', $organisationId)
                ->whereNotNull('trips.started_at')
                ->whereNull('trips.ended_at')
                ->when(
                    $ctx['branchScopeEnabled'] && Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id'),
                    fn ($q) => $q->whereIn('vehicles.branch_id', $ctx['accessibleBranchIds'])
                )
                ->orderByDesc('trips.started_at')
                ->select(
                    'trips.id as trip_id',
                    'trips.started_at',
                    'vehicles.id as vehicle_id',
                    'vehicles.name as vehicle_name',
                    'vehicles.registration_number',
                    'users.id as driver_id',
                    'users.first_name as driver_first_name',
                    'users.last_name as driver_last_name'
                )
                ->get();
        }

        return view('sharpfleet.admin.trips.active', [
            'tripsTableExists' => $tripsTableExists,
            'trips' => $trips,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');
        if (!$user) {
            abort(403);
        }

        $role = Roles::normalize($user['role'] ?? null);
        $canEditBookings = in_array($role, [Roles::COMPANY_ADMIN, Roles::BRANCH_ADMIN, Roles::BOOKING_ADMIN], true);
        if (!$canEditBookings) {
            abort(403);
        }

        $isDriver = $role === Roles::DRIVER;

        $validated = $request->validate([
            'user_id' => $isDriver ? ['required', 'integer'] : ['nullable', 'integer'],
            'vehicle_id' => ['required', 'integer'],
            'branch_id' => ['nullable', 'integer'],
            'planned_start_date' => ['required', 'date'],
            'planned_start_hour' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:23'],
            'planned_start_minute' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:59'],
            'planned_end_date' => ['required', 'date', 'after_or_equal:planned_start_date'],
            'planned_end_hour' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:23'],
            'planned_end_minute' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:59'],
            'customer_id' => ['nullable', 'integer'],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
            'remind_me' => ['nullable'],
        ]);
        if ($isDriver) {
            $validated['user_id'] = (int) ($user['id'] ?? 0);
        } else {
            $userId = isset($validated['user_id']) ? (int) $validated['user_id'] : 0;
            $validated['user_id'] = $userId > 0 ? $userId : null;
        }

        $startTime = sprintf('%02d:%02d', (int) $validated['planned_start_hour'], (int) $validated['planned_start_minute']);
        $endTime = sprintf('%02d:%02d', (int) $validated['planned_end_hour'], (int) $validated['planned_end_minute']);

        $plannedStart = $validated['planned_start_date'] . ' ' . $startTime . ':00';
        $plannedEnd = $validated['planned_end_date'] . ' ' . $endTime . ':00';

        $organisationId = (int) ($user['organisation_id'] ?? 0);
        $ctx = $this->branchAccessContext($user);
        $branchId = isset($validated['branch_id']) && $validated['branch_id'] !== null && $validated['branch_id'] !== ''
            ? (int) $validated['branch_id']
            : null;

        $this->assertBranchAccessible($branchId, $ctx);
        $this->assertVehicleAccessible($organisationId, (int) $validated['vehicle_id'], $ctx);

        $this->bookingService->createBooking($organisationId, [
            'user_id' => $validated['user_id'] ?? null,
            'vehicle_id' => (int) $validated['vehicle_id'],
            'branch_id' => $branchId,
            'planned_start' => $plannedStart,
            'planned_end' => $plannedEnd,
            'customer_id' => $validated['customer_id'] ?? null,
            'customer_name' => $validated['customer_name'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'remind_me' => $request->boolean('remind_me'),
            'created_by_user_id' => (int) ($user['id'] ?? 0),
        ], $user);

        return redirect('/app/sharpfleet/admin/bookings')->with('success', 'Booking created.');
    }

    public function update(Request $request, $booking)
    {
        $user = $request->session()->get('sharpfleet.user');
        $role = $user ? Roles::normalize($user['role'] ?? null) : null;
        $canEditBookings = $role && in_array($role, [Roles::COMPANY_ADMIN, Roles::BRANCH_ADMIN, Roles::BOOKING_ADMIN], true);
        if (!$user || !$canEditBookings) {
            abort(403);
        }

        $organisationId = (int) ($user['organisation_id'] ?? 0);
        $ctx = $this->branchAccessContext($user);
        $this->assertBookingAccessible($organisationId, (int) $booking, $ctx);

        $validated = $request->validate([
            'user_id' => ['nullable', 'integer'],
            'vehicle_id' => ['required', 'integer'],
            'branch_id' => ['nullable', 'integer'],
            'planned_start_date' => ['required', 'date'],
            'planned_start_hour' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:23'],
            'planned_start_minute' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:59'],
            'planned_end_date' => ['required', 'date', 'after_or_equal:planned_start_date'],
            'planned_end_hour' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:23'],
            'planned_end_minute' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:59'],
            'customer_id' => ['nullable', 'integer'],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
            'remind_me' => ['nullable'],
        ]);
        $userId = isset($validated['user_id']) ? (int) $validated['user_id'] : 0;
        $validated['user_id'] = $userId > 0 ? $userId : null;

        $startTime = sprintf('%02d:%02d', (int) $validated['planned_start_hour'], (int) $validated['planned_start_minute']);
        $endTime = sprintf('%02d:%02d', (int) $validated['planned_end_hour'], (int) $validated['planned_end_minute']);

        $plannedStart = $validated['planned_start_date'] . ' ' . $startTime . ':00';
        $plannedEnd = $validated['planned_end_date'] . ' ' . $endTime . ':00';

        $branchId = isset($validated['branch_id']) && $validated['branch_id'] !== null && $validated['branch_id'] !== ''
            ? (int) $validated['branch_id']
            : null;
        $this->assertBranchAccessible($branchId, $ctx);
        $this->assertVehicleAccessible($organisationId, (int) $validated['vehicle_id'], $ctx);

        $this->bookingService->updateBooking($organisationId, (int) $booking, [
            'user_id' => $validated['user_id'] ?? null,
            'vehicle_id' => (int) $validated['vehicle_id'],
            'branch_id' => $branchId,
            'planned_start' => $plannedStart,
            'planned_end' => $plannedEnd,
            'customer_id' => $validated['customer_id'] ?? null,
            'customer_name' => $validated['customer_name'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'remind_me' => $request->boolean('remind_me'),
            'updated_by_user_id' => (int) ($user['id'] ?? 0),
        ], $user);

        return redirect('/app/sharpfleet/admin/bookings')->with('success', 'Booking updated.');
    }

    public function cancel(Request $request, $booking)
    {
        $user = $request->session()->get('sharpfleet.user');
        $role = $user ? Roles::normalize($user['role'] ?? null) : null;
        $canEditBookings = $role && in_array($role, [Roles::COMPANY_ADMIN, Roles::BRANCH_ADMIN, Roles::BOOKING_ADMIN], true);
        if (!$user || !$canEditBookings) {
            abort(403);
        }

        $organisationId = (int) ($user['organisation_id'] ?? 0);
        $ctx = $this->branchAccessContext($user);
        $this->assertBookingAccessible($organisationId, (int) $booking, $ctx);

        $this->bookingService->cancelBooking($organisationId, (int) $booking, $user, true);

        return redirect('/app/sharpfleet/admin/bookings')->with('success', 'Booking cancelled.');
    }

    public function changeVehicle(Request $request, $booking)
    {
        $user = $request->session()->get('sharpfleet.user');
        $role = $user ? Roles::normalize($user['role'] ?? null) : null;
        $canEditBookings = $role && in_array($role, [Roles::COMPANY_ADMIN, Roles::BRANCH_ADMIN, Roles::BOOKING_ADMIN], true);
        if (!$user || !$canEditBookings) {
            abort(403);
        }

        $validated = $request->validate([
            'new_vehicle_id' => ['required', 'integer'],
        ]);

        $organisationId = (int) ($user['organisation_id'] ?? 0);
        $ctx = $this->branchAccessContext($user);
        $this->assertBookingAccessible($organisationId, (int) $booking, $ctx);
        $this->assertVehicleAccessible($organisationId, (int) $validated['new_vehicle_id'], $ctx);

        $this->bookingService->changeBookingVehicle(
            $organisationId,
            (int) $booking,
            (int) $validated['new_vehicle_id']
        );

        return redirect('/app/sharpfleet/admin/bookings')->with('success', 'Booking vehicle updated.');
    }

    public function availableVehicles(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');
        if (!$user || !Roles::isAdminPortal($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer'],
            'planned_start_date' => ['required', 'date'],
            'planned_start_hour' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:23'],
            'planned_start_minute' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:59'],
            'planned_end_date' => ['required', 'date'],
            'planned_end_hour' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:23'],
            'planned_end_minute' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:59'],
        ]);

        $startTime = sprintf('%02d:%02d', (int) $validated['planned_start_hour'], (int) $validated['planned_start_minute']);
        $endTime = sprintf('%02d:%02d', (int) $validated['planned_end_hour'], (int) $validated['planned_end_minute']);

        $organisationId = (int) $user['organisation_id'];
        $branchId = isset($validated['branch_id']) && $validated['branch_id'] !== null && $validated['branch_id'] !== ''
            ? (int) $validated['branch_id']
            : null;

        $ctx = $this->branchAccessContext($user);
        if ($ctx['branchScopeEnabled'] && (!$branchId || $branchId <= 0)) {
            $branchId = (int) ($ctx['accessibleBranchIds'][0] ?? 0);
            $branchId = $branchId > 0 ? $branchId : null;
        }
        $this->assertBranchAccessible($branchId, $ctx);

        $branchesService = new BranchService();
        $tz = (new CompanySettingsService($organisationId))->timezone();
        if ($branchesService->branchesEnabled() && $branchId && $branchId > 0) {
            $branch = $branchesService->getBranch($organisationId, $branchId);
            if ($branch && isset($branch->timezone) && trim((string) $branch->timezone) !== '') {
                $tz = (string) $branch->timezone;
            }
        }

        $plannedStart = Carbon::createFromFormat('Y-m-d H:i:s', $validated['planned_start_date'] . ' ' . $startTime . ':00', $tz);
        $plannedEnd = Carbon::createFromFormat('Y-m-d H:i:s', $validated['planned_end_date'] . ' ' . $endTime . ':00', $tz);

        $vehicles = $this->bookingService->getAvailableVehicles($organisationId, $plannedStart, $plannedEnd, $branchId);

        return response()->json([
            'vehicles' => $vehicles,
        ]);
    }
}
