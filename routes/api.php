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

// 🔧 STRIPPED AUTH TEST — removed Sanctum middleware just to check route reachability
Route::get('/test-vehicles-auth', function () {
    dd('👋 Route is alive and reachable');
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
