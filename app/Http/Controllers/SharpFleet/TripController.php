<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\TripService;
use App\Http\Requests\SharpFleet\Trips\StartTripRequest;
use Illuminate\Http\RedirectResponse;

class TripController extends Controller
{
    protected TripService $tripService;

    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

    /**
     * Start a trip (Driver UI – session based)
     */
    public function start(StartTripRequest $request): RedirectResponse
    {
        $user = session('sharpfleet.user');

        if (!$user) {
            abort(401, 'Not authenticated');
        }

        $this->tripService->startTrip(
            $user,
            $request->validated()
        );

        return redirect('/app/sharpfleet/driver')
            ->with('success', 'Trip started successfully');
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
