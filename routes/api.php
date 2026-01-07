<?php

use App\Http\Controllers\Api\DeviceAuditApiController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileTripController;
use App\Http\Controllers\Api\MobileVehicleController;
use App\Services\SharpFleet\VehicleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// Public test route to list vehicles without auth
Route::get('/test-vehicles', function (VehicleService $vehicleService) {
    try {
        Log::info('[TestVehicles] Request received');

        $organisationId = 3; // hardcoded known-good organisation ID
        Log::info("[TestVehicles] Using organisation ID: $organisationId");

        $vehicles = $vehicleService->getAvailableVehicles($organisationId);
        Log::info('[TestVehicles] Vehicles fetched:', ['count' => $vehicles->count()]);

        $payload = $vehicles->map(function ($v) {
            $id = (int) ($v->id ?? 0);
            $make = property_exists($v, 'make') ? trim((string) $v->make) : '';
            $model = property_exists($v, 'model') ? trim((string) $v->model) : '';
            $rego = property_exists($v, 'registration_number') ? trim((string) $v->registration_number) : '';
            $label = trim("$make $model");
            $label = $rego ? "$label – $rego" : $label;

            return [
                'id' => $id,
                'label' => $label,
            ];
        })->values();

        return response()->json(['vehicles' => $payload]);

    } catch (\Throwable $e) {
        Log::error('[TestVehicles] Error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'error' => true,
            'message' => $e->getMessage(),
        ], 500);
    }
});

// Existing routes
Route::post('/device-audit', [DeviceAuditApiController::class, 'store']);
Route::post('/mobile/login', [MobileAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/mobile/trips', [MobileTripController::class, 'store']);
    Route::get('/mobile/me', fn (Request $request) => $request->user());
    Route::get('/mobile/vehicles', [MobileVehicleController::class, 'index']);
    Route::post('/mobile/logout', function (Request $request) {
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json([
            'status' => 'logged_out',
        ]);
    });
});
