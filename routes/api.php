<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\DeviceAuditApiController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileTripController;
use App\Http\Controllers\Api\MobileVehicleController;
use App\Http\Controllers\Api\TripStartConfigController;
use App\Http\Controllers\Api\MobileCustomerController;

/*
|--------------------------------------------------------------------------
| Public / Bootstrap Endpoints
|--------------------------------------------------------------------------
*/

// Mobile login (returns API key)
Route::post('/mobile/login', [MobileAuthController::class, 'login']);

// Device audit (first-run / diagnostics)
Route::post('/device-audit', [DeviceAuditApiController::class, 'store']);


/*
|--------------------------------------------------------------------------
| Authenticated Mobile API (API Key)
|--------------------------------------------------------------------------
*/
Route::middleware('api.key')->group(function () {

    // 👤 Current user
    Route::get('/mobile/me', fn (Request $request) => $request->user());

    // 🚗 Vehicles
    Route::get('/mobile/vehicles', [MobileVehicleController::class, 'index']);
    Route::get(
        '/mobile/vehicles/{vehicle}/last_reading',
        [MobileVehicleController::class, 'lastReading']
    );

    // ▶️ Trips
    Route::post('/mobile/trips', [MobileTripController::class, 'store']);
    Route::post('/mobile/trips/sync', [MobileTripController::class, 'sync']);

    // ⚙️ Trip start configuration
    Route::get('/mobile/trips/start-config', TripStartConfigController::class);

    // 🧾 Customers
    Route::get('/mobile/customers', [MobileCustomerController::class, 'index']);

    // 🔓 Logout (client discards key)
    Route::post('/mobile/logout', fn () =>
        response()->json(['status' => 'logged_out'])
    );
});
