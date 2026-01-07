<?php

use App\Http\Controllers\Api\DeviceAuditApiController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileTripController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/device-audit', [DeviceAuditApiController::class, 'store']);

Route::post('/mobile/login', [MobileAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/mobile/trips', [MobileTripController::class, 'store']);

    Route::get('/mobile/me', fn (Request $request) => $request->user());

    // 🧪 Temporary debug version of /mobile/vehicles to test auth + routing
    Route::get('/mobile/vehicles', function (Request $request) {
        return response()->json([
            'user_id' => $request->user()?->id,
            'vehicles' => [
                ['id' => 1, 'label' => '🚗 Test Car'],
                ['id' => 2, 'label' => '🚙 Demo Truck'],
            ]
        ]);
    });

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
