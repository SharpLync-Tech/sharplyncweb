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
     * ✅ Mobile API (offline-first): sync completed trips captured offline.
     *
     * Accepts:
     *  - { "trip": { ... } }  (single)
     *  - { "trips": [ ... ] } (batch)
     *
     * Mobile payload can send either:
     *  - started_at / ended_at (preferred)
     *  - OR start_time / end_time (we map these)
     *
     * Required for a "completed trip" sync:
     * - vehicle_id
     * - start_km
     * - end_km
     * - started_at/ended_at OR start_time/end_time
     */
    public function sync(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof SharpFleetUser) {
            abort(403, 'Invalid user context.');
        }

        Log::info('[MobileTripController] /mobile/trips/sync hit', [
            'user_id' => $user->id ?? null,
            'org_id' => $user->organisation_id ?? null,
            'has_trip' => $request->has('trip'),
            'has_trips' => $request->has('trips'),
        ]);

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
            Log::warning('[MobileTripController] No trips provided');
            return response()->json([
                'message' => 'No trips provided.',
            ], 422);
        }

        Log::info('[MobileTripController] Raw trips received', [
            'count' => count($rawTrips),
            'first_trip_keys' => array_keys($rawTrips[0] ?? []),
            'first_trip' => $rawTrips[0] ?? null,
        ]);

        // Map mobile keys to what TripService::syncOfflineTrips expects
        $mappedTrips = array_map(function (array $t) {
            $vehicleId = (int) Arr::get($t, 'vehicle_id', 0);

            $startedAt = Arr::get($t, 'started_at');
            $endedAt   = Arr::get($t, 'ended_at');

            // Mobile uses start_time/end_time
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

        Log::info('[MobileTripController] Mapped trips', [
            'count' => count($mappedTrips),
            'first_mapped' => $mappedTrips[0] ?? null,
        ]);

        // Validate core fields before passing into service
        foreach ($mappedTrips as $i => $t) {
            $idx = $i + 1;

            if (empty($t['vehicle_id']) || (int) $t['vehicle_id'] <= 0) {
                Log::warning('[MobileTripController] Rejecting trip: missing vehicle_id', [
                    'trip_index' => $idx,
                    'trip' => $t,
                ]);

                return response()->json([
                    'message' => "Trip #{$idx} missing vehicle_id.",
                ], 422);
            }

            if (empty($t['started_at']) || empty($t['ended_at'])) {
                Log::warning('[MobileTripController] Rejecting trip: missing started_at/ended_at', [
                    'trip_index' => $idx,
                    'trip' => $t,
                ]);

                return response()->json([
                    'message' => "Trip #{$idx} missing started_at/ended_at (or start_time/end_time).",
                ], 422);
            }

            // For THIS mobile sync flow, we require readings.
            // If a tenant allows blanks later, we can relax this.
            if ($t['start_km'] === null || $t['start_km'] === '' || $t['end_km'] === null || $t['end_km'] === '') {
                Log::warning('[MobileTripController] Rejecting trip: missing start_km/end_km', [
                    'trip_index' => $idx,
                    'trip' => $t,
                ]);

                return response()->json([
                    'message' => "Trip #{$idx} missing start_km/end_km.",
                ], 422);
            }
        }

        try {
            $result = $this->tripService->syncOfflineTrips($user->toArray(), $mappedTrips);

            Log::info('[MobileTripController] TripService::syncOfflineTrips completed', [
                'result' => $result,
            ]);

            return response()->json([
                'status' => 'ok',
                'synced_count' => is_array($mappedTrips) ? count($mappedTrips) : 0,
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('[MobileTripController] syncOfflineTrips failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Trip sync failed on server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
