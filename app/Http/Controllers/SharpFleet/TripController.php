<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\TripService;

class TripController extends Controller
{
    protected TripService $tripService;

    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

    public function start()
    {
        // $this->tripService->startTrip()
    }

    public function end()
    {
        // $this->tripService->endTrip()
    }

    public function edit($trip)
    {
        // $this->tripService->editTrip()
    }
}
