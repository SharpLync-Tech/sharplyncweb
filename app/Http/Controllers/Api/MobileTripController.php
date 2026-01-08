<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SharpFleet\User as SharpFleetUser;
use App\Services\SharpFleet\TripService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class MobileTripController extends Controller
{
    public function __construct(private TripService $tripService)
    {
    }

    /**
     * Mobile API (legacy/live): start a trip for the authenticated driver.
     * Keeps existing behaviour for web-style "start trip now" flow.
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

            // If you ever enable odometer on mobile live-start flow
            'start_km' => ['nullable', 'integer', 'min:0'],
            'distance_method' => ['nullable', 'string', 'max:50'],
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

    /**
     * ✅ Mobile API (offline-first): sync completed trips captured offline.
     *
     * Accepts:
     *  - { "trip": { ... } }  (single)
     *  - { "trips": [ ... ] } (batch)
     *
     * Mobile payload can send either:
     *  - started_at / ended_at (preferred)
     *  - OR start_time / end_time (we map these)
     */
    public function sync(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof SharpFleetUser) {
            abort(403, 'Invalid user context.');
        }

        $validated = $request->validate([
            'trip' => ['nullable', 'array'],
            'trips' => ['nullable', 'array'],
            'trips.*' => ['array'],
        ]);

        $rawTrips = [];

        if (!empty($validated['trip']) && is_array($validated['trip'])) {
            $rawTrips[] = $validated['trip'];
        }

        if (!empty($validated['trips']) && is_array($validated['trips'])) {
            foreach ($validated['trips'] as $t) {
                if (is_array($t)) $rawTrips[] = $t;
            }
        }

        if (count($rawTrips) === 0) {
            return response()->json([
                'message' => 'No trips provided.',
            ], 422);
        }

        // Map mobile keys to what TripService::syncOfflineTrips expects
        $mappedTrips = array_map(function (array $t) {
            // Prefer canonical keys; allow mobile aliases.
            $vehicleId = (int) Arr::get($t, 'vehicle_id', 0);

            $startedAt = Arr::get($t, 'started_at');
            $endedAt   = Arr::get($t, 'ended_at');

            // Mobile currently uses start_time/end_time
            if (!$startedAt) $startedAt = Arr::get($t, 'start_time');
            if (!$endedAt)   $endedAt   = Arr::get($t, 'end_time');

            return [
                'vehicle_id' => $vehicleId,
                'trip_mode'  => Arr::get($t, 'trip_mode', 'business'),

                // REQUIRED for many tenants. Mobile must provide these to succeed.
                'start_km'   => Arr::get($t, 'start_km'),
                'end_km'     => Arr::get($t, 'end_km'),

                'started_at' => $startedAt,
                'ended_at'   => $endedAt,

                // Optional customer fields if you later add them to mobile
                'customer_id' => Arr::get($t, 'customer_id'),
                'customer_name' => Arr::get($t, 'customer_name'),
                'client_present' => Arr::get($t, 'client_present'),
                'client_address' => Arr::get($t, 'client_address'),
                'purpose_of_travel' => Arr::get($t, 'purpose_of_travel'),
            ];
        }, $rawTrips);

        // Validate core fields before passing into service
        foreach ($mappedTrips as $i => $t) {
            if (empty($t['vehicle_id']) || (int) $t['vehicle_id'] <= 0) {
                return response()->json([
                    'message' => "Trip #".($i+1)." missing vehicle_id.",
                ], 422);
            }
            if (empty($t['started_at']) || empty($t['ended_at'])) {
                return response()->json([
                    'message' => "Trip #".($i+1)." missing started_at/ended_at (or start_time/end_time).",
                ], 422);
            }
            // We do NOT enforce km here because tenant settings vary,
            // but TripService will enforce it and return a proper 422 error if required.
        }

        $result = $this->tripService->syncOfflineTrips($user->toArray(), $mappedTrips);

        return response()->json([
            'status' => 'ok',
            'result' => $result,
        ]);
    }
}
