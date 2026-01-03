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
                ->orderBy('name')
                ->limit(500)
                ->get();
        }

        $result = $this->bookingService->getUpcomingBookings($organisationId);

        return view('sharpfleet.driver.bookings.upcoming', [
            'bookingsTableExists' => $result['tableExists'],
            'bookings' => $result['bookings'],
            'vehicles' => $vehicles,
            'customersTableExists' => $customersTableExists,
            'customers' => $customers,
            'branchesEnabled' => $branchesEnabled,
            'branches' => $branches,
            'defaultTimezone' => $defaultTimezone,
        ]);
    }

    public function availableVehicles(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');
        if (!$user) {
            abort(403, 'Login required');
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

        $validated = $request->validate([
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
        }

        $this->bookingService->createBooking($organisationId, [
            'user_id' => (int) $user['id'],
            'vehicle_id' => (int) $validated['vehicle_id'],
            'branch_id' => $branchId,
            'planned_start' => $plannedStart,
            'planned_end' => $plannedEnd,
            'customer_id' => $validated['customer_id'] ?? null,
            'customer_name' => $validated['customer_name'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect('/app/sharpfleet/bookings')->with('success', 'Booking created.');
    }

    public function cancel(Request $request, $booking)
    {
        $user = $request->session()->get('sharpfleet.user');
        if (!$user) {
            abort(403, 'Login required');
        }

        $this->bookingService->cancelBooking((int) $user['organisation_id'], (int) $booking, $user, false);

        return redirect('/app/sharpfleet/bookings')->with('success', 'Booking cancelled.');
    }

    public function startTrip()
    {
        // Intentionally not implemented yet.
        // Drivers start trips from the Driver Dashboard; booking enforcement happens server-side.
        abort(404);
    }
}
