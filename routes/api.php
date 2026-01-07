<?php

use App\Http\Controllers\Api\DeviceAuditApiController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileTripController;
use App\Http\Controllers\Api\MobileVehicleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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