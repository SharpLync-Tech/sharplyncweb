<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SharpFleet\User as SharpFleetUser;
use App\Services\SharpFleet\TripService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class MobileTripController extends Controller
{
    public function __construct(private TripService $tripService)
    {
    }

    /**
     * Mobile API (legacy/live): start a trip
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof SharpFleetUser) {
            abort(403, 'Invalid user context.');
        }

        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer', 'min:1'],
            'trip_mode' => ['nullable', 'string', 'max:50'],
            'started_at' => ['nullable', 'date'],
            'start_km' => ['nullable', 'integer', 'min:0'],
        ]);

        $trip = $this->tripService->startTrip($user->toArray(), $validated);

        return response()->json([
            'trip' => [
                'id' => (int) $trip->id,
                'vehicle_id' => (int) $trip->vehicle_id,
                'started_at' => optional($trip->started_at)->toIso8601String(),
            ],
        ]);
    }

    /**
     * 🔒 Mobile API: sync completed offline trips
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

        if (!empty($validated['trip'])) {
            $rawTrips[] = $validated['trip'];
        }

        if (!empty($validated['trips'])) {
            foreach ($validated['trips'] as $t) {
                $rawTrips[] = $t;
            }
        }

        if (count($rawTrips) === 0) {
            return response()->json([
                'message' => 'No trips provided.',
            ], 422);
        }

        $mappedTrips = [];

        foreach ($rawTrips as $index => $t) {
            $mapped = [
                'vehicle_id' => Arr::get($t, 'vehicle_id'),
                'trip_mode' => Arr::get($t, 'trip_mode', 'business'),

                'start_km' => Arr::get($t, 'start_km'),
                'end_km' => Arr::get($t, 'end_km'),

                'started_at' => Arr::get($t, 'started_at') ?? Arr::get($t, 'start_time'),
                'ended_at' => Arr::get($t, 'ended_at') ?? Arr::get($t, 'end_time'),
            ];

            // 🔒 HARD VALIDATION
            foreach (['vehicle_id', 'start_km', 'end_km', 'started_at', 'ended_at'] as $field) {
                if ($mapped[$field] === null || $mapped[$field] === '') {
                    Log::warning('[MobileTripSync] ❌ Rejected trip payload', [
                        'index' => $index,
                        'missing_field' => $field,
                        'payload' => $t,
                        'user_id' => $user->id,
                    ]);

                    return response()->json([
                        'message' => "Trip #".($index + 1)." missing required field: {$field}",
                    ], 422);
                }
            }

            $mappedTrips[] = $mapped;
        }

        $result = $this->tripService->syncOfflineTrips(
            $user->toArray(),
            $mappedTrips
        );

        return response()->json([
            'status' => 'ok',
            'synced' => count($mappedTrips),
            'result' => $result,
        ]);
    }
}
