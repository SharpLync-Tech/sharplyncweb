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
    | SharpFleet Home
    |--------------------------------------------------------------------------
    */
    Route::get('/', function (\Illuminate\Http\Request $request) {

        if ($request->session()->has('sharpfleet.user')) {
            $role = $request->session()->get('sharpfleet.user.role');

            return match ($role) {
                'admin'  => redirect('/app/sharpfleet/admin'),
                'driver' => redirect('/app/sharpfleet/driver'),
                default  => redirect('/app/sharpfleet/login'),
            };
        }

        return view('sharpfleet.home');
    });

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */
    Route::get('/login', [AuthController::class, 'showLogin']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | Admin Routes (ADMIN ONLY)
    |--------------------------------------------------------------------------
    */
    Route::middleware(\App\Http\Middleware\SharpFleetAdminAuth::class)
        ->prefix('admin')
        ->group(function () {

            Route::get('/', function () {
                return view('sharpfleet.admin.dashboard');
            });

            Route::get('/vehicles', [VehicleController::class, 'index']);
            Route::post('/vehicles', [VehicleController::class, 'store']);
            Route::post('/vehicles/{vehicle}/archive', [VehicleController::class, 'archive']);

            Route::get('/customers', [CustomerController::class, 'index']);
            Route::post('/customers', [CustomerController::class, 'store']);

            Route::get('/bookings', [AdminBookingController::class, 'index']);
            Route::post('/bookings', [AdminBookingController::class, 'store']);
            Route::post('/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel']);

            Route::get('/faults', [AdminFaultController::class, 'index']);
            Route::post('/faults/{fault}/status', [AdminFaultController::class, 'updateStatus']);

            Route::get('/reports/trips', [ReportController::class, 'trips']);
            Route::get('/reports/vehicles', [ReportController::class, 'vehicles']);

            Route::get('/register', function () {
                return view('sharpfleet.admin.register');
            });
        });

    /*
    |--------------------------------------------------------------------------
    | Driver Routes (DRIVER ONLY)
    |--------------------------------------------------------------------------
    */
    Route::middleware(\App\Http\Middleware\SharpFleetDriverAuth::class)
        ->group(function () {

            Route::get('/driver', function () {
                return view('sharpfleet.driver.dashboard');
            });

            // Trips
            Route::post('/trips/start', [TripController::class, 'start']);
            Route::post('/trips/end', [TripController::class, 'end']);
            Route::post('/trips/{trip}/edit', [TripController::class, 'edit']);

            // Faults
            Route::post('/faults/from-trip', [FaultController::class, 'storeFromTrip']);
            Route::post('/faults/standalone', [FaultController::class, 'storeStandalone']);

            // Bookings
            Route::get('/bookings/upcoming', [BookingController::class, 'upcoming']);
            Route::post('/bookings/start-trip', [BookingController::class, 'startTrip']);
        });

    /*
    |--------------------------------------------------------------------------
    | Debug (ADMIN ONLY)
    |--------------------------------------------------------------------------
    */
    Route::get('/debug', function () {
        return view('sharpfleet.debug');
    })->middleware(\App\Http\Middleware\SharpFleetAdminAuth::class);
});
