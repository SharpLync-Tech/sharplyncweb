<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SharpFleet\AuthController;
use App\Http\Controllers\SharpFleet\TripController;
use App\Http\Controllers\SharpFleet\FaultController;
use App\Http\Controllers\SharpFleet\BookingController;

use App\Http\Controllers\SharpFleet\Admin\VehicleController;
use App\Http\Controllers\SharpFleet\Admin\CustomerController;
use App\Http\Controllers\SharpFleet\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\SharpFleet\Admin\FaultController as AdminFaultController;
use App\Http\Controllers\SharpFleet\Admin\ReportController;

/*
|--------------------------------------------------------------------------
| SharpFleet Routes
|--------------------------------------------------------------------------
*/

Route::prefix('app/sharpfleet')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | 🔹 SharpFleet Dashboard (SAFE – NO DB)
    |--------------------------------------------------------------------------
    */
    Route::get('/', function () {
        return view('sharpfleet.dashboard');
    })->middleware('auth');

    /*
    |--------------------------------------------------------------------------
    | Authentication (Temporary Local Auth)
    |--------------------------------------------------------------------------
    */
    Route::get('/login', [AuthController::class, 'showLogin']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/debug', function () {
        return view('sharpfleet.debug');
    })->middleware('auth');

    /*
    |--------------------------------------------------------------------------
    | Driver – Trips
    |--------------------------------------------------------------------------
    */
    Route::post('/trips/start', [TripController::class, 'start']);
    Route::post('/trips/end', [TripController::class, 'end']);
    Route::post('/trips/{trip}/edit', [TripController::class, 'edit']);

    /*
    |--------------------------------------------------------------------------
    | Driver – Faults
    |--------------------------------------------------------------------------
    */
    Route::post('/faults/from-trip', [FaultController::class, 'storeFromTrip']);
    Route::post('/faults/standalone', [FaultController::class, 'storeStandalone']);

    /*
    |--------------------------------------------------------------------------
    | Driver – Bookings
    |--------------------------------------------------------------------------
    */
    Route::get('/bookings/upcoming', [BookingController::class, 'upcoming']);
    Route::post('/bookings/start-trip', [BookingController::class, 'startTrip']);

    /*
    |--------------------------------------------------------------------------
    | Admin – Vehicles
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/vehicles', [VehicleController::class, 'index']);
    Route::post('/admin/vehicles', [VehicleController::class, 'store']);
    Route::post('/admin/vehicles/{vehicle}/archive', [VehicleController::class, 'archive']);

    /*
    |--------------------------------------------------------------------------
    | Admin – Customers
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/customers', [CustomerController::class, 'index']);
    Route::post('/admin/customers', [CustomerController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | Admin – Bookings
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/bookings', [AdminBookingController::class, 'index']);
    Route::post('/admin/bookings', [AdminBookingController::class, 'store']);
    Route::post('/admin/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel']);

    /*
    |--------------------------------------------------------------------------
    | Admin – Faults
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/faults', [AdminFaultController::class, 'index']);
    Route::post('/admin/faults/{fault}/status', [AdminFaultController::class, 'updateStatus']);

    /*
    |--------------------------------------------------------------------------
    | Admin – Reports
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/reports/trips', [ReportController::class, 'trips']);
    Route::get('/admin/reports/vehicles', [ReportController::class, 'vehicles']);

    /*
    |--------------------------------------------------------------------------
    | DEV / TEST ROUTE (SAFE)
    |--------------------------------------------------------------------------
    */
    Route::get('/test/start-trip', function () {
        return app(\App\Services\SharpFleet\TripService::class)
            ->startTrip([
                'vehicle_id' => 1,
                'trip_mode'  => 'no_client',
                'start_km'   => 99999,
            ]);
    });

    /*
    |--------------------------------------------------------------------------
    | SharpFleet Admin (SAFE – isolated)
    |--------------------------------------------------------------------------
    */

    Route::prefix('app/sharpfleet/admin')->group(function () {

        Route::get('/login', function () {
            return view('sharpfleet.admin.login');
        });

        Route::post('/login', function () {
            // TEMP: reuse existing auth, no logic yet
            return redirect('/app/sharpfleet/admin');
        });

        Route::get('/', function () {
            return view('sharpfleet.admin.dashboard');
        });

        Route::get('/register', function () {
            return view('sharpfleet.admin.register');
        });

    });


});
