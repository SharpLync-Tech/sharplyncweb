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
     * 🔄 Mobile API: sync completed offline trips (FULL DEBUG)
     */
    public function sync(Request $request): JsonResponse
    {
        Log::info('[MobileTripSync] 🔔 Sync endpoint hit');

        $user = $request->user();
        if (!$user instanceof SharpFleetUser) {
            Log::error('[MobileTripSync] ❌ Invalid user context');
            abort(403, 'Invalid user context.');
        }

        Log::info('[MobileTripSync] 👤 User resolved', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        Log::info('[MobileTripSync] 📦 Raw request payload', $request->all());

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

        Log::info('[MobileTripSync] 🧮 Trips received', [
            'count' => count($rawTrips),
        ]);

        if (count($rawTrips) === 0) {
            Log::warning('[MobileTripSync] ❌ No trips provided');
            return response()->json(['message' => 'No trips provided'], 422);
        }

        $mappedTrips = [];

        foreach ($rawTrips as $i => $t) {
            Log::info("[MobileTripSync] 🔎 Processing trip #".($i + 1), $t);

            $mapped = [
                'vehicle_id' => Arr::get($t, 'vehicle_id'),
                'trip_mode' => Arr::get($t, 'trip_mode', 'business'),
                'start_km' => Arr::get($t, 'start_km'),
                'end_km' => Arr::get($t, 'end_km'),
                'started_at' => Arr::get($t, 'started_at') ?? Arr::get($t, 'start_time'),
                'ended_at' => Arr::get($t, 'ended_at') ?? Arr::get($t, 'end_time'),
            ];

            foreach (['vehicle_id', 'start_km', 'end_km', 'started_at', 'ended_at'] as $field) {
                if ($mapped[$field] === null || $mapped[$field] === '') {
                    Log::error('[MobileTripSync] ❌ Missing required field', [
                        'field' => $field,
                        'trip_index' => $i,
                        'payload' => $mapped,
                    ]);

                    return response()->json([
                        'message' => "Trip #".($i + 1)." missing field: {$field}",
                    ], 422);
                }
            }

            $mappedTrips[] = $mapped;
        }

        Log::info('[MobileTripSync] 🚀 Passing trips to TripService', $mappedTrips);

        $result = $this->tripService->syncOfflineTrips(
            $user->toArray(),
            $mappedTrips
        );

        Log::info('[MobileTripSync] ✅ TripService result', [
            'result' => $result,
        ]);

        return response()->json([
            'status' => 'ok',
            'synced' => count($mappedTrips),
            'result' => $result,
        ]);
    }
}
