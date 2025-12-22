<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BookingService;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index()
    {
        // $this->bookingService->getUpcomingBookings()
    }

    public function store()
    {
        // $this->bookingService->createBooking()
    }

    public function cancel($booking)
    {
        // Booking cancellation logic later
    }
}
