<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BookingService;
use App\Services\SharpFleet\BranchService;
use App\Services\SharpFleet\CompanySettingsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    private function applyTimeInputs(Request $request): void
    {
        // Support mobile-friendly <input type="time"> while remaining compatible with legacy hour/minute selects.
        $startTime = trim((string) $request->input('planned_start_time', ''));
        if ($startTime !== '' && (string) $request->input('planned_start_hour', '') === '' && (string) $request->input('planned_start_minute', '') === '') {
            if (preg_match('/^(\d{1,2}):(\d{2})$/', $startTime, $m) === 1) {
                $request->merge([
                    'planned_start_hour' => str_pad((string) ((int) $m[1]), 2, '0', STR_PAD_LEFT),
                    'planned_start_minute' => str_pad((string) ((int) $m[2]), 2, '0', STR_PAD_LEFT),
                ]);
            }
        }

        $endTime = trim((string) $request->input('planned_end_time', ''));
        if ($endTime !== '' && (string) $request->input('planned_end_hour', '') === '' && (string) $request->input('planned_end_minute', '') === '') {
            if (preg_match('/^(\d{1,2}):(\d{2})$/', $endTime, $m) === 1) {
                $request->merge([
                    'planned_end_hour' => str_pad((string) ((int) $m[1]), 2, '0', STR_PAD_LEFT),
                    'planned_end_minute' => str_pad((string) ((int) $m[2]), 2, '0', STR_PAD_LEFT),
                ]);
            }
        }
    }

    private function assertCustomerInBranchIfSupported(int $organisationId, ?int $customerId, ?int $branchId): void
    {
        if (!$customerId || $customerId <= 0) {
            return;
        }

        if (!$branchId || $branchId <= 0) {
            return;
        }

        if (!Schema::connection('sharpfleet')->hasTable('customers')) {
            return;
        }

        if (!Schema::connection('sharpfleet')->hasColumn('customers', 'branch_id')) {
            return;
        }

        $customerBranchId = DB::connection('sharpfleet')
            ->table('customers')
            ->where('organisation_id', $organisationId)
            ->where('id', $customerId)
            ->value('branch_id');

        $customerBranchId = $customerBranchId !== null && $customerBranchId !== '' ? (int) $customerBranchId : null;
        if ($customerBranchId && $customerBranchId > 0 && (int) $customerBranchId !== (int) $branchId) {
            throw ValidationException::withMessages([
                'customer_id' => 'Selected customer does not belong to your branch.',
            ]);
        }
    }

    public function upcoming(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');
        if (!$user) {
            abort(403, 'Login required');
        }

        $organisationId = (int) $user['organisation_id'];

        $branchesService = new BranchService();
        $branchesEnabled = $branchesService->branchesEnabled();
        $branches = $branchesEnabled ? $branchesService->getBranchesForUser($organisationId, (int) $user['id']) : collect();
        if ($branchesEnabled && $branchesService->userBranchAccessEnabled() && $branches->count() === 0) {
            abort(403, 'No branch access.');
        }

        $branchAccessEnabled = $branchesEnabled
            && $branchesService->vehiclesHaveBranchSupport()
            && $branchesService->userBranchAccessEnabled();
        $accessibleBranchIds = $branchAccessEnabled
            ? $branchesService->getAccessibleBranchIdsForUser($organisationId, (int) $user['id'])
            : [];

        // getBranchesForUser() orders default first (when schema supports it)
        $defaultBranch = $branchesEnabled ? $branches->first() : null;
        $defaultTimezone = $defaultBranch && isset($defaultBranch->timezone) && trim((string) $defaultBranch->timezone) !== ''
            ? (string) $defaultBranch->timezone
            : (new CompanySettingsService($organisationId))->timezone();

        $vehicles = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->when(
                $branchAccessEnabled && Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id'),
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

        $customersTableExists = Schema::connection('sharpfleet')->hasTable('customers');
        $customers = collect();
        if ($customersTableExists) {
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

        $result = $this->bookingService->getUpcomingBookings($organisationId, $user);

        $editBooking = null;
        $editId = (int) $request->query('edit', 0);
        if ($editId > 0 && Schema::connection('sharpfleet')->hasTable('bookings')) {
            $row = DB::connection('sharpfleet')
                ->table('bookings')
                ->where('organisation_id', $organisationId)
                ->where('id', $editId)
                ->first();

            if (!$row) {
                abort(404);
            }

            if ((int) ($row->user_id ?? 0) !== (int) ($user['id'] ?? 0)) {
                abort(403);
            }

            if ((string) ($row->status ?? '') !== 'planned') {
                abort(403);
            }

            $tz = isset($row->timezone) && trim((string) ($row->timezone ?? '')) !== ''
                ? (string) $row->timezone
                : $defaultTimezone;

            $startLocal = Carbon::parse((string) $row->planned_start)->utc()->timezone($tz);
            $endLocal = Carbon::parse((string) $row->planned_end)->utc()->timezone($tz);

            $editBooking = [
                'id' => (int) $row->id,
                'branch_id' => isset($row->branch_id) ? (int) ($row->branch_id ?? 0) : null,
                'vehicle_id' => (int) ($row->vehicle_id ?? 0),
                'customer_id' => isset($row->customer_id) ? (int) ($row->customer_id ?? 0) : null,
                'customer_name' => (string) ($row->customer_name ?? ''),
                'notes' => (string) ($row->notes ?? ''),
                'planned_start_date' => $startLocal->format('Y-m-d'),
                'planned_start_time' => $startLocal->format('H:i'),
                'planned_end_date' => $endLocal->format('Y-m-d'),
                'planned_end_time' => $endLocal->format('H:i'),
            ];
        }

        return view('sharpfleet.driver.bookings.upcoming', [
            'bookingsTableExists' => $result['tableExists'],
            'bookings' => $result['bookings'],
            'vehicles' => $vehicles,
            'customersTableExists' => $customersTableExists,
            'customers' => $customers,
            'branchesEnabled' => $branchesEnabled,
            'branches' => $branches,
            'defaultTimezone' => $defaultTimezone,
            'editBooking' => $editBooking,
        ]);
    }

    public function availableVehicles(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');
        if (!$user) {
            abort(403, 'Login required');
        }

        $this->applyTimeInputs($request);

        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer'],
            'planned_start_date' => ['required', 'date'],
            'planned_start_time' => ['nullable', 'regex:/^\d{1,2}:\d{2}$/'],
            'planned_start_hour' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:23'],
            'planned_start_minute' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:59'],
            'planned_end_date' => ['required', 'date'],
            'planned_end_time' => ['nullable', 'regex:/^\d{1,2}:\d{2}$/'],
            'planned_end_hour' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:23'],
            'planned_end_minute' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:59'],
        ]);

        $startTime = sprintf('%02d:%02d', (int) $validated['planned_start_hour'], (int) $validated['planned_start_minute']);
        $endTime = sprintf('%02d:%02d', (int) $validated['planned_end_hour'], (int) $validated['planned_end_minute']);

        $organisationId = (int) $user['organisation_id'];
        $branchId = isset($validated['branch_id']) && $validated['branch_id'] !== null && $validated['branch_id'] !== ''
            ? (int) $validated['branch_id']
            : null;

        $branchesService = new BranchService();

        if ($branchesService->branchesEnabled() && $branchesService->userBranchAccessEnabled()) {
            $branchesForUser = $branchesService->getBranchesForUser($organisationId, (int) $user['id']);
            if ($branchesForUser->count() === 0) {
                abort(403, 'No branch access.');
            }

            if ($branchId === null) {
                $branchId = (int) ($branchesForUser->first()->id ?? 0);
            }

            if ($branchId && !$branchesService->userCanAccessBranch($organisationId, (int) $user['id'], (int) $branchId)) {
                abort(403, 'No branch access.');
            }
        }

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

    public function store(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');
        if (!$user) {
            abort(403, 'Login required');
        }

        $this->applyTimeInputs($request);

        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer'],
            'branch_id' => ['nullable', 'integer'],
            'planned_start_date' => ['required', 'date'],
            'planned_start_time' => ['nullable', 'regex:/^\d{1,2}:\d{2}$/'],
            'planned_start_hour' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:23'],
            'planned_start_minute' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:59'],
            'planned_end_date' => ['required', 'date', 'after_or_equal:planned_start_date'],
            'planned_end_time' => ['nullable', 'regex:/^\d{1,2}:\d{2}$/'],
            'planned_end_hour' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:23'],
            'planned_end_minute' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:59'],
            'customer_id' => ['nullable', 'integer'],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
        ]);

        $startTime = sprintf('%02d:%02d', (int) $validated['planned_start_hour'], (int) $validated['planned_start_minute']);
        $endTime = sprintf('%02d:%02d', (int) $validated['planned_end_hour'], (int) $validated['planned_end_minute']);

        $plannedStart = $validated['planned_start_date'] . ' ' . $startTime . ':00';
        $plannedEnd = $validated['planned_end_date'] . ' ' . $endTime . ':00';

        $organisationId = (int) $user['organisation_id'];
        $branchesService = new BranchService();
        $branchId = isset($validated['branch_id']) && $validated['branch_id'] !== null && $validated['branch_id'] !== ''
            ? (int) $validated['branch_id']
            : null;

        if ($branchesService->branchesEnabled() && $branchesService->userBranchAccessEnabled()) {
            $branchesForUser = $branchesService->getBranchesForUser($organisationId, (int) $user['id']);
            if ($branchesForUser->count() === 0) {
                abort(403, 'No branch access.');
            }

            if ($branchId === null) {
                $branchId = (int) ($branchesForUser->first()->id ?? 0);
            }

            if ($branchId && !$branchesService->userCanAccessBranch($organisationId, (int) $user['id'], (int) $branchId)) {
                throw ValidationException::withMessages([
                    'branch_id' => 'You do not have access to that branch.',
                ]);
            }

            // If branches are installed, ensure selected vehicle belongs to selected branch.
            if ($branchesService->vehiclesHaveBranchSupport() && $branchId && $branchId > 0) {
                $vehicleBranchId = $branchesService->getBranchIdForVehicle($organisationId, (int) $validated['vehicle_id']);
                if ($vehicleBranchId && (int) $vehicleBranchId !== (int) $branchId) {
                    throw ValidationException::withMessages([
                        'vehicle_id' => 'Selected vehicle does not belong to your branch.',
                    ]);
                }
            }
        }

        $effectiveBranchId = $branchId;
        if ($branchesService->branchesEnabled() && $branchesService->vehiclesHaveBranchSupport() && (!$effectiveBranchId || $effectiveBranchId <= 0)) {
            $effectiveBranchId = $branchesService->getBranchIdForVehicle($organisationId, (int) $validated['vehicle_id']);
            $effectiveBranchId = $effectiveBranchId ? (int) $effectiveBranchId : null;
        }
        $this->assertCustomerInBranchIfSupported($organisationId, isset($validated['customer_id']) ? (int) $validated['customer_id'] : null, $effectiveBranchId);

        $this->bookingService->createBooking($organisationId, [
            'user_id' => (int) $user['id'],
            'vehicle_id' => (int) $validated['vehicle_id'],
            'branch_id' => $branchId,
            // Pass local date/time strings; BookingService resolves timezone and stores UTC.
            'planned_start' => $plannedStart,
            'planned_end' => $plannedEnd,
            'customer_id' => $validated['customer_id'] ?? null,
            'customer_name' => $validated['customer_name'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ], $user);

        $previousUrl = url()->previous();
        $redirectTo = str_contains($previousUrl, '/app/sharpfleet/mobile')
            ? '/app/sharpfleet/mobile/bookings'
            : '/app/sharpfleet/bookings';

        return redirect($redirectTo)->with('success', 'Booking created.');
    }

    public function cancel(Request $request, $booking)
    {
        $user = $request->session()->get('sharpfleet.user');
        if (!$user) {
            abort(403, 'Login required');
        }

        $this->bookingService->cancelBooking((int) $user['organisation_id'], (int) $booking, $user, false);

        $previousUrl = url()->previous();
        $redirectTo = str_contains($previousUrl, '/app/sharpfleet/mobile')
            ? '/app/sharpfleet/mobile/bookings'
            : '/app/sharpfleet/bookings';

        return redirect($redirectTo)->with('success', 'Booking cancelled.');
    }

    public function update(Request $request, $booking)
    {
        $user = $request->session()->get('sharpfleet.user');
        if (!$user) {
            abort(403, 'Login required');
        }

        $this->applyTimeInputs($request);

        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer'],
            'branch_id' => ['nullable', 'integer'],
            'planned_start_date' => ['required', 'date'],
            'planned_start_time' => ['nullable', 'regex:/^\d{1,2}:\d{2}$/'],
            'planned_start_hour' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:23'],
            'planned_start_minute' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:59'],
            'planned_end_date' => ['required', 'date', 'after_or_equal:planned_start_date'],
            'planned_end_time' => ['nullable', 'regex:/^\d{1,2}:\d{2}$/'],
            'planned_end_hour' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:23'],
            'planned_end_minute' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:59'],
            'customer_id' => ['nullable', 'integer'],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
        ]);

        $organisationId = (int) $user['organisation_id'];
        $bookingId = (int) $booking;

        if (!Schema::connection('sharpfleet')->hasTable('bookings')) {
            abort(404);
        }

        $row = DB::connection('sharpfleet')
            ->table('bookings')
            ->where('organisation_id', $organisationId)
            ->where('id', $bookingId)
            ->first();

        if (!$row) {
            abort(404);
        }

        if ((int) ($row->user_id ?? 0) !== (int) ($user['id'] ?? 0)) {
            abort(403);
        }

        $startTime = sprintf('%02d:%02d', (int) $validated['planned_start_hour'], (int) $validated['planned_start_minute']);
        $endTime = sprintf('%02d:%02d', (int) $validated['planned_end_hour'], (int) $validated['planned_end_minute']);
        $plannedStart = $validated['planned_start_date'] . ' ' . $startTime . ':00';
        $plannedEnd = $validated['planned_end_date'] . ' ' . $endTime . ':00';

        $branchesService = new BranchService();
        $branchId = isset($validated['branch_id']) && $validated['branch_id'] !== null && $validated['branch_id'] !== ''
            ? (int) $validated['branch_id']
            : null;

        if ($branchesService->branchesEnabled() && $branchesService->userBranchAccessEnabled()) {
            $branchesForUser = $branchesService->getBranchesForUser($organisationId, (int) $user['id']);
            if ($branchesForUser->count() === 0) {
                abort(403, 'No branch access.');
            }

            if ($branchId === null) {
                $branchId = (int) ($branchesForUser->first()->id ?? 0);
            }

            if ($branchId && !$branchesService->userCanAccessBranch($organisationId, (int) $user['id'], (int) $branchId)) {
                throw ValidationException::withMessages([
                    'branch_id' => 'You do not have access to that branch.',
                ]);
            }

            if ($branchesService->vehiclesHaveBranchSupport() && $branchId && $branchId > 0) {
                $vehicleBranchId = $branchesService->getBranchIdForVehicle($organisationId, (int) $validated['vehicle_id']);
                if ($vehicleBranchId && (int) $vehicleBranchId !== (int) $branchId) {
                    throw ValidationException::withMessages([
                        'vehicle_id' => 'Selected vehicle does not belong to your branch.',
                    ]);
                }
            }
        }

        $effectiveBranchId = $branchId;
        if ($branchesService->branchesEnabled() && $branchesService->vehiclesHaveBranchSupport() && (!$effectiveBranchId || $effectiveBranchId <= 0)) {
            $effectiveBranchId = $branchesService->getBranchIdForVehicle($organisationId, (int) $validated['vehicle_id']);
            $effectiveBranchId = $effectiveBranchId ? (int) $effectiveBranchId : null;
        }
        $this->assertCustomerInBranchIfSupported($organisationId, isset($validated['customer_id']) ? (int) $validated['customer_id'] : null, $effectiveBranchId);

        $this->bookingService->updateBooking($organisationId, $bookingId, [
            'user_id' => (int) $user['id'],
            'vehicle_id' => (int) $validated['vehicle_id'],
            'branch_id' => $branchId,
            // Pass local date/time strings; BookingService resolves timezone and stores UTC.
            'planned_start' => $plannedStart,
            'planned_end' => $plannedEnd,
            'customer_id' => $validated['customer_id'] ?? null,
            'customer_name' => $validated['customer_name'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'updated_by_user_id' => (int) ($user['id'] ?? 0),
        ], $user);

        $previousUrl = url()->previous();
        $redirectTo = str_contains($previousUrl, '/app/sharpfleet/mobile')
            ? '/app/sharpfleet/mobile/bookings'
            : '/app/sharpfleet/bookings';

        return redirect($redirectTo)->with('success', 'Booking updated.');
    }

    public function startTrip()
    {
        // Intentionally not implemented yet.
        // Drivers start trips from the Driver Dashboard; booking enforcement happens server-side.
        abort(404);
    }
}
