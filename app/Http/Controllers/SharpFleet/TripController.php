<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\TripService;
use App\Http\Requests\SharpFleet\Trips\StartTripRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

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

    /**
     * End a trip (Driver UI – session based)
     */
    public function end(Request $request): RedirectResponse
    {
        $request->validate([
            'trip_id' => ['required', 'integer'],
            'end_km'  => ['required', 'integer', 'min:0'],
        ]);

        $user = session('sharpfleet.user');

        if (!$user) {
            abort(401, 'Not authenticated');
        }

        $this->tripService->endTrip(
            $user,
            $request->only(['trip_id', 'end_km'])
        );

        return redirect('/app/sharpfleet/driver')
            ->with('success', 'Trip ended successfully');
    }

    /**
     * Sync one or more completed offline trips (Driver UI – session based)
     */
    public function offlineSync(Request $request): JsonResponse
    {
        $user = session('sharpfleet.user');

        if (!$user) {
            abort(401, 'Not authenticated');
        }

        $validated = $request->validate([
            'trips' => ['required', 'array', 'min:1'],
            'trips.*.vehicle_id' => ['required', 'integer'],
            'trips.*.trip_mode' => ['required', 'string'],
            'trips.*.start_km' => ['required', 'integer', 'min:0'],
            'trips.*.end_km' => ['required', 'integer', 'min:0'],
            'trips.*.started_at' => ['required', 'date'],
            'trips.*.ended_at' => ['required', 'date'],
            'trips.*.customer_id' => ['nullable', 'integer'],
            'trips.*.customer_name' => ['nullable', 'string', 'max:150'],
            'trips.*.client_present' => ['nullable'],
            'trips.*.client_address' => ['nullable', 'string'],
        ]);

        $result = $this->tripService->syncOfflineTrips($user, $validated['trips']);

        return response()->json($result);
    }

    public function edit($trip)
    {
        // to be implemented later
    }
}
