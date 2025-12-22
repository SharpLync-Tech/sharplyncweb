<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BookingService;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function upcoming()
    {
        // $this->bookingService->getUpcomingBookings()
    }

    public function startTrip()
    {
        // $this->bookingService->startTripFromBooking()
    }
}
