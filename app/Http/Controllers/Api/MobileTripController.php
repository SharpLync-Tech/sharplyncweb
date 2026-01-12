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
     * Mobile API (legacy/live): start a trip for the authenticated driver.
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

            'customer_id' => ['nullable', 'integer', 'min:1'],
            'customer_name' => ['nullable', 'string', 'max:150'],

            'client_present' => ['nullable'],
            'client_address' => ['nullable', 'string', 'max:255'],

            'purpose_of_travel' => ['nullable', 'string', 'max:255'],

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
                if (is_array($t)) {
                    $rawTrips[] = $t;
                }
            }
        }

        if (count($rawTrips) === 0) {
            Log::warning('[MobileTripSync] No trips provided', [
                'user_id' => $user->id ?? null,
                'org_id' => $user->organisation_id ?? null,
            ]);

            return response()->json([
                'message' => 'No trips provided.',
            ], 422);
        }

        // Map mobile keys to what TripService::syncOfflineTrips expects
        $mappedTrips = array_map(function (array $t) {
            $vehicleId = (int) Arr::get($t, 'vehicle_id', 0);

            $startedAt = Arr::get($t, 'started_at');
            $endedAt   = Arr::get($t, 'ended_at');

            // Mobile aliases
            if (!$startedAt) $startedAt = Arr::get($t, 'start_time');
            if (!$endedAt)   $endedAt   = Arr::get($t, 'end_time');

            return [
                'vehicle_id' => $vehicleId,
                'trip_mode'  => Arr::get($t, 'trip_mode', 'business'),

                'start_km'   => Arr::get($t, 'start_km'),
                'end_km'     => Arr::get($t, 'end_km'),

                'started_at' => $startedAt,
                'ended_at'   => $endedAt,

                'customer_id' => Arr::get($t, 'customer_id'),
                'customer_name' => Arr::get($t, 'customer_name'),
                'client_present' => Arr::get($t, 'client_present'),
                'client_address' => Arr::get($t, 'client_address'),
                'purpose_of_travel' => Arr::get($t, 'purpose_of_travel'),
            ];
        }, $rawTrips);

        // Loud debug: log what we are about to send to TripService
        Log::info('[MobileTripSync] Incoming trips mapped', [
            'user_id' => $user->id ?? null,
            'org_id' => $user->organisation_id ?? null,
            'count' => count($mappedTrips),
            'trips' => $mappedTrips,
        ]);

        // Validate core fields before passing into service
        foreach ($mappedTrips as $i => $t) {
            if (empty($t['vehicle_id']) || (int) $t['vehicle_id'] <= 0) {
                Log::warning('[MobileTripSync] Missing vehicle_id', [
                    'index' => $i,
                    'trip' => $t,
                ]);

                return response()->json([
                    'message' => "Trip #".($i+1)." missing vehicle_id.",
                ], 422);
            }

            if (empty($t['started_at']) || empty($t['ended_at'])) {
                Log::warning('[MobileTripSync] Missing started_at/ended_at', [
                    'index' => $i,
                    'trip' => $t,
                ]);

                return response()->json([
                    'message' => "Trip #".($i+1)." missing started_at/ended_at (or start_time/end_time).",
                ], 422);
            }

            // We lock schema: offline sync must provide readings
            if ($t['start_km'] === null || $t['start_km'] === '' || !is_numeric($t['start_km'])) {
                Log::warning('[MobileTripSync] Missing/invalid start_km', [
                    'index' => $i,
                    'trip' => $t,
                ]);

                return response()->json([
                    'message' => "Trip #".($i+1)." missing or invalid start_km.",
                ], 422);
            }

            if ($t['end_km'] === null || $t['end_km'] === '' || !is_numeric($t['end_km'])) {
                Log::warning('[MobileTripSync] Missing/invalid end_km', [
                    'index' => $i,
                    'trip' => $t,
                ]);

                return response()->json([
                    'message' => "Trip #".($i+1)." missing or invalid end_km.",
                ], 422);
            }
        }

        $result = $this->tripService->syncOfflineTrips($user->toArray(), $mappedTrips);

        Log::info('[MobileTripSync] TripService result', [
            'user_id' => $user->id ?? null,
            'org_id' => $user->organisation_id ?? null,
            'result' => $result,
        ]);

        $synced = is_array($result['synced'] ?? null) ? $result['synced'] : [];
        $skipped = is_array($result['skipped'] ?? null) ? $result['skipped'] : [];

        if (count($synced) + count($skipped) === 0) {
            Log::warning('[MobileTripSync] WARNING: Service returned empty synced/skipped arrays', [
                'user_id' => $user->id ?? null,
                'org_id' => $user->organisation_id ?? null,
                'mappedTrips' => $mappedTrips,
                'result' => $result,
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'result' => $result,
        ]);
    }
}
