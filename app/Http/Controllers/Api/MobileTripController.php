<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SharpFleet\User as SharpFleetUser;
use App\Services\SharpFleet\TripService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileTripController extends Controller
{
    public function __construct(private TripService $tripService)
    {
    }

    /**
     * Mobile API: start a trip for the authenticated driver.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof SharpFleetUser) {
            abort(403, 'Invalid user context.');
        }

        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer', 'min:1'],

            // Optional fields supported by TripService::startTrip
            'trip_mode' => ['nullable', 'string', 'max:50'],
            'started_at' => ['nullable', 'date'],

            'customer_id' => ['nullable', 'integer', 'min:1'],
            'customer_name' => ['nullable', 'string', 'max:150'],

            'client_present' => ['nullable'],
            'client_address' => ['nullable', 'string', 'max:255'],

            'purpose_of_travel' => ['nullable', 'string', 'max:255'],
        ]);

        $trip = $this->tripService->startTrip($user->toArray(), $validated);

        return response()->json([
            'trip' => [
                'id' => (int) $trip->id,
                'organisation_id' => (int) $trip->organisation_id,
                'user_id' => (int) $trip->user_id,
                'vehicle_id' => (int) $trip->vehicle_id,
                'trip_mode' => $trip->trip_mode,
                'started_at' => optional($trip->started_at)->toIso8601String(),
                'timezone' => $trip->timezone,
            ],
        ]);
    }
}
