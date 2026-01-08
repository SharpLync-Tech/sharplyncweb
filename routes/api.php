<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceAuditApiController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileTripController;
use App\Http\Controllers\Api\MobileVehicleController;
use App\Models\SharpFleet\User as SharpFleetUser;
use App\Services\SharpFleet\VehicleService;

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
Route::middleware('auth:sanctum')->get('/test-vehicles-auth', function (Request $request, VehicleService $vehicleService) {
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

// ✅ Mobile login endpoint
Route::post('/mobile/login', [MobileAuthController::class, 'login']);

// ✅ Device audit endpoint
Route::post('/device-audit', [DeviceAuditApiController::class, 'store']);

// ✅ Authenticated mobile endpoints using Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/mobile/trips', [MobileTripController::class, 'store']);

    Route::get('/mobile/me', fn (Request $request) => $request->user());

    Route::get('/mobile/vehicles', [MobileVehicleController::class, 'index']);

    Route::post('/mobile/logout', function (Request $request) {
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json(['status' => 'logged_out']);
    });
});

// ✅ NEW NO-AUTH PUBLIC VEHICLE ENDPOINT — for debugging
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

// ✅ VEHICLE LIST — Protected by Custom API Key
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
