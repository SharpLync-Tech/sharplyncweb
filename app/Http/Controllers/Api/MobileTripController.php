<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\TripService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MobileTripController extends Controller
{
    public function __construct(
        private TripService $tripService
    ) {}

    /**
     * Sync completed offline trips
     */
    public function sync(Request $request)
    {
        $user = $request->user();

        Log::info('[MobileTripSync] Incoming payload', [
            'user_id' => $user?->id,
            'body' => $request->all(),
        ]);

        $trips = $request->input('trips');

        if (!is_array($trips) || count($trips) === 0) {
            Log::warning('[MobileTripSync] No trips provided');
            return response()->json([
                'error' => 'No trips provided',
            ], 422);
        }

        try {
            $result = $this->tripService->syncOfflineTrips(
                $user->toArray(),
                $trips
            );

            Log::info('[MobileTripSync] Result', $result);

            return response()->json($result);
        } catch (ValidationException $e) {
            Log::error('[MobileTripSync] Validation failed', [
                'errors' => $e->errors(),
            ]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error('[MobileTripSync] Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Trip sync failed',
            ], 500);
        }
    }
}
