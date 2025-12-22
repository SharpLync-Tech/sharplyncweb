<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\TripService;
use App\Http\Requests\SharpFleet\Trips\StartTripRequest;
use Illuminate\Http\JsonResponse;


class TripController extends Controller
{
    protected TripService $tripService;

    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

    public function start(StartTripRequest $request): JsonResponse
{
    $trip = $this->tripService->startTrip($request->validated());

    return response()->json([
        'success' => true,
        'trip_id' => $trip->id,
    ]);
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
