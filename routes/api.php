<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceAuditApiController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileTripController;
use App\Http\Controllers\Api\MobileVehicleController;
use App\Models\SharpFleet\User as SharpFleetUser;
use Laravel\Sanctum\PersonalAccessToken;
use App\Services\SharpFleet\VehicleService;

// 🚨 UNAUTHENTICATED TEST ENDPOINT
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

// 🧪 DEEP DEBUG: Token check and user resolution
Route::get('/debug-token', function (Request $request) {
    $header = $request->header('Authorization');
    Log::info("[DebugToken] Auth header: $header");

    if (!$header || !str_starts_with($header, 'Bearer ')) {
        return response()->json(['error' => 'Missing or invalid Bearer token'], 401);
    }

    $accessToken = substr($header, 7);
    $tokenModel = PersonalAccessToken::findToken($accessToken);
    if (!$tokenModel) {
        return response()->json(['error' => 'Token not found in DB'], 401);
    }

    $user = $tokenModel->tokenable;

    return response()->json([
        'user_id' => $user?->id,
        'user_type' => get_class($user),
        'token_abilities' => $tokenModel->abilities,
    ]);
});

// ✅ Device audit
Route::post('/device-audit', [DeviceAuditApiController::class, 'store']);

// ✅ Login endpoint
Route::post('/mobile/login', [MobileAuthController::class, 'login']);

// ✅ Protected endpoints
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
