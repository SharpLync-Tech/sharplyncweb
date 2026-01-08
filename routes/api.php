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

// 🚨 UNAUTHENTICATED TEST ENDPOINT — confirms vehicle service works without login
Route::get('/test-vehicles', function (VehicleService $vehicleService) {
    $vehicles = $vehicleService->getAvailableVehicles(3); // Replace 3 with known org ID if needed

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

// 🧪 AUTHENTICATED TEST ENDPOINT — bypasses middleware to debug token directly
Route::get('/test-vehicles-auth', function (Request $request, VehicleService $vehicleService) {
    try {
        $header = $request->header('Authorization');
        Log::info("[TestAuth] Auth header: $header");

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json(['error' => 'No bearer token'], 401);
        }

        $accessToken = substr($header, 7);
        $tokenModel = PersonalAccessToken::findToken($accessToken);
        if (!$tokenModel) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        $user = $tokenModel->tokenable;
        Log::info("[TestAuth] Token resolved to user", ['id' => $user->id ?? null, 'class' => get_class($user)]);

        if (!$user instanceof SharpFleetUser) {
            return response()->json(['error' => 'Wrong user class'], 403);
        }

        $organisationId = (int) ($user->organisation_id ?? 0);
        Log::info("[TestAuth] Org ID: $organisationId");

        $vehicles = $vehicleService->getAvailableVehicles($organisationId);
        Log::info('[TestAuth] Vehicles fetched', ['count' => $vehicles->count()]);

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

    } catch (\Throwable $e) {
        Log::error('[TestAuth] Error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
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