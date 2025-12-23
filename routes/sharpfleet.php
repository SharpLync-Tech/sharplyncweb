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
use App\Http\Controllers\SharpFleet\Admin\CompanySettingsController;
use App\Http\Controllers\SharpFleet\Admin\CompanyController;
use App\Http\Controllers\SharpFleet\Admin\CompanyProfileController;
use App\Http\Controllers\SharpFleet\Admin\CompanySafetyCheckController;

Route::prefix('app/sharpfleet')->group(function () {

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

    Route::get('/login', [AuthController::class, 'showLogin']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware(\App\Http\Middleware\SharpFleetAdminAuth::class)
        ->prefix('admin')
        ->group(function () {

            Route::get('/', fn () => view('sharpfleet.admin.dashboard'));

            Route::get('/company', [CompanyController::class, 'index']);
            Route::get('/company/profile', [CompanyProfileController::class, 'edit']);
            Route::post('/company/profile', [CompanyProfileController::class, 'update']);

            Route::get('/safety-checks', [CompanySafetyCheckController::class, 'index']);
            Route::post('/safety-checks', [CompanySafetyCheckController::class, 'update']);

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

            Route::get('/settings', [CompanySettingsController::class, 'edit']);
            Route::post('/settings', [CompanySettingsController::class, 'update']);

            Route::get('/register', fn () => view('sharpfleet.admin.register'));
        });

    Route::middleware(\App\Http\Middleware\SharpFleetDriverAuth::class)
        ->group(function () {

            Route::get('/driver', fn () => view('sharpfleet.driver.dashboard'));

            Route::post('/trips/start', [TripController::class, 'start']);
            Route::post('/trips/end', [TripController::class, 'end']);

            Route::post('/faults/from-trip', [FaultController::class, 'storeFromTrip']);
            Route::post('/faults/standalone', [FaultController::class, 'storeStandalone']);

            Route::get('/bookings/upcoming', [BookingController::class, 'upcoming']);
            Route::post('/bookings/start-trip', [BookingController::class, 'startTrip']);
        });

    Route::get('/debug', fn () => view('sharpfleet.debug'))
        ->middleware(\App\Http\Middleware\SharpFleetAdminAuth::class);
});
