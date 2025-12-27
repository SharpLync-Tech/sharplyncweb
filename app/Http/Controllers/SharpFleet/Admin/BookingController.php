<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BookingService;
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

    public function index(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');
        if (!$user || $user['role'] !== 'admin') {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) $user['organisation_id'];

        $vehicles = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $drivers = DB::connection('sharpfleet')
            ->table('users')
            ->where('organisation_id', $organisationId)
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
                        $qq
                            ->where('role', 'admin')
                            ->where('is_driver', 1);
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
                ->orderBy('name')
                ->limit(500)
                ->get();
        }

        $result = $this->bookingService->getUpcomingBookings($organisationId);

        return view('sharpfleet.admin.bookings.index', [
            'bookingsTableExists' => $result['tableExists'],
            'bookings' => $result['bookings'],
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'customersTableExists' => $customersTableExists,
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');
        if (!$user || $user['role'] !== 'admin') {
            abort(403, 'Admin access only');
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer'],
            'vehicle_id' => ['required', 'integer'],
            'planned_start_date' => ['required', 'date'],
            'planned_start_time' => ['required', 'date_format:H:i'],
            'planned_end_date' => ['required', 'date'],
            'planned_end_time' => ['required', 'date_format:H:i'],
            'customer_id' => ['nullable', 'integer'],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
        ]);

        $plannedStart = $validated['planned_start_date'] . ' ' . $validated['planned_start_time'] . ':00';
        $plannedEnd = $validated['planned_end_date'] . ' ' . $validated['planned_end_time'] . ':00';

        $this->bookingService->createBooking((int) $user['organisation_id'], [
            'user_id' => (int) $validated['user_id'],
            'vehicle_id' => (int) $validated['vehicle_id'],
            'planned_start' => $plannedStart,
            'planned_end' => $plannedEnd,
            'customer_id' => $validated['customer_id'] ?? null,
            'customer_name' => $validated['customer_name'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect('/app/sharpfleet/admin/bookings')->with('success', 'Booking created.');
    }

    public function cancel(Request $request, $booking)
    {
        $user = $request->session()->get('sharpfleet.user');
        if (!$user || $user['role'] !== 'admin') {
            abort(403, 'Admin access only');
        }

        $this->bookingService->cancelBooking((int) $user['organisation_id'], (int) $booking, $user, true);

        return redirect('/app/sharpfleet/admin/bookings')->with('success', 'Booking cancelled.');
    }
}
