<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceAuditApiController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileTripController;
use App\Http\Controllers\Api\MobileVehicleController;
use App\Http\Controllers\Api\TripStartConfigController;
use App\Models\SharpFleet\User as SharpFleetUser;
use App\Services\SharpFleet\VehicleService;

Route::post('/_debug/ping', function () {
    \Log::info('[DEBUG PING] Hit');
    return response()->json(['ok' => true]);
});


// 🚨 UNAUTHENTICATED TEST ENDPOINT — confirms vehicle service works without login
Route::get('/test-vehicles', function (VehicleService $vehicleService) {
    $vehicles = $vehicleService->getAvailableVehicles(3);

    $payload = $vehicles->map(function ($v) {
        $id = (int) ($v->id ?? 0);
        $make = property_exists($v, 'make') ? trim((string) $v->make) : '';
        $model = property_exists($v, 'model') ? trim((string) $v->model) : '';
        $rego = property_exists($v, 'registration_number') ? trim((string) $v->registration_number) : '';
        $label = trim("$make $model");
        $label = $rego ? "$label – $rego" : $label;

        return ['id' => $id, 'label' => $label];
    })->values();

    return response()->json(['vehicles' => $payload]);
});


// ✅ AUTH TEST USING SANCTUM — logs user and stops before further logic
Route::middleware('auth:sanctum')->get('/test-vehicles-auth', function (Request $request) {
    try {
        $user = $request->user();
        Log::info("[SanctumAuth] ✅ Route hit. Resolved user:", [
            'id' => $user->id ?? null,
            'class' => get_class($user),
            'email' => $user->email ?? null,
        ]);

        return response()->json([
            'status' => 'OK',
            'user_id' => $user->id ?? null,
            'user_class' => get_class($user),
            'email' => $user->email ?? null,
        ]);
    } catch (\Throwable $e) {
        Log::error('[SanctumAuth] ❌ Exception hit:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'error' => 'Exception occurred',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// ✅ API KEY AUTH TEST ENDPOINT
Route::middleware('api.key')->get('/test-api-key', function () {
    return response()->json(['status' => 'API key auth OK']);
});

// ✅ Mobile login endpoint (returns API key)
Route::post('/mobile/login', [MobileAuthController::class, 'login']);

// ✅ Device audit endpoint
Route::post('/device-audit', [DeviceAuditApiController::class, 'store']);


// ======================================================
// ✅ AUTHENTICATED MOBILE ENDPOINTS (API KEY BASED)
// ======================================================
Route::middleware('api.key')->group(function () {

    Route::get('/mobile/me', fn (Request $request) => $request->user());

    // 🚗 Vehicles
    Route::get('/mobile/vehicles', [MobileVehicleController::class, 'index']);

    Route::get(
        '/mobile/vehicles/{vehicle}/last_reading',
        [MobileVehicleController::class, 'lastReading']
    );

    // ▶️ START TRIP (live / online)
    Route::post('/mobile/trips', [MobileTripController::class, 'store']);

    // 🔄 SYNC COMPLETED / OFFLINE TRIPS
    Route::post('/mobile/trips/sync', [MobileTripController::class, 'sync']);

    // ⚙️ Trip start configuration (NEW)
    Route::get('/mobile/trips/start-config', TripStartConfigController::class);

    // 🔓 Mobile logout (API key clients can simply discard key)
    Route::post('/mobile/logout', function () {
        return response()->json(['status' => 'logged_out']);
    });
});


// ✅ NO-AUTH PUBLIC VEHICLE ENDPOINT — debugging only
Route::get('/vehicles-public', function (VehicleService $vehicleService) {
    $vehicles = $vehicleService->getAvailableVehicles(3);

    $payload = $vehicles->map(function ($v) {
        $id = (int) ($v->id ?? 0);
        $make = property_exists($v, 'make') ? trim((string) $v->make) : '';
        $model = property_exists($v, 'model') ? trim((string) $v->model) : '';
        $rego = property_exists($v, 'registration_number') ? trim((string) $v->registration_number) : '';
        $label = trim("$make $model");
        $label = $rego ? "$label – $rego" : $label;

        return ['id' => $id, 'label' => $label];
    });

    return response()->json(['vehicles' => $payload]);
});


// ✅ VEHICLE ENDPOINT USING API KEY AUTH (legacy / testing)
Route::middleware('api.key')->get('/vehicles-api-key', function (VehicleService $vehicleService) {
    $vehicles = $vehicleService->getAvailableVehicles(3);

    $payload = $vehicles->map(function ($v) {
        $id = (int) ($v->id ?? 0);
        $make = property_exists($v, 'make') ? trim((string) $v->make) : '';
        $model = property_exists($v, 'model') ? trim((string) $v->model) : '';
        $rego = property_exists($v, 'registration_number') ? trim((string) $v->registration_number) : '';
        $label = trim("$make $model");
        $label = $rego ? "$label – $rego" : $label;

        return ['id' => $id, 'label' => $label];
    })->values();

    return response()->json(['vehicles' => $payload]);
});
