<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $vehicles = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
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
        ]);
    }

    public function availableVehicles(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');
        if (!$user) {
            abort(403, 'Login required');
        }

        $validated = $request->validate([
            'planned_start_date' => ['required', 'date'],
            'planned_start_hour' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:23'],
            'planned_start_minute' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:59'],
            'planned_end_date' => ['required', 'date'],
            'planned_end_hour' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:23'],
            'planned_end_minute' => ['required', 'regex:/^\d{1,2}$/', 'numeric', 'min:0', 'max:59'],
        ]);

        $startTime = sprintf('%02d:%02d', (int) $validated['planned_start_hour'], (int) $validated['planned_start_minute']);
        $endTime = sprintf('%02d:%02d', (int) $validated['planned_end_hour'], (int) $validated['planned_end_minute']);

        $plannedStart = Carbon::parse($validated['planned_start_date'] . ' ' . $startTime . ':00');
        $plannedEnd = Carbon::parse($validated['planned_end_date'] . ' ' . $endTime . ':00');

        $vehicles = $this->bookingService->getAvailableVehicles((int) $user['organisation_id'], $plannedStart, $plannedEnd);

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
            'planned_start_date' => ['required', 'date', 'after_or_equal:today'],
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

        $this->bookingService->createBooking((int) $user['organisation_id'], [
            'user_id' => (int) $user['id'],
            'vehicle_id' => (int) $validated['vehicle_id'],
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
