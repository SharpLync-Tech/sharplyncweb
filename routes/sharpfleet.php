<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\SharpFleet\AuthController;
use App\Http\Controllers\SharpFleet\SsoController;
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
use App\Http\Controllers\SharpFleet\Admin\RegisterController;
use App\Http\Controllers\SharpFleet\Admin\UserController;
use App\Http\Controllers\SharpFleet\Admin\DriverInviteController as AdminDriverInviteController;
use App\Http\Controllers\SharpFleet\DriverInviteController;

/*
|--------------------------------------------------------------------------
| SharpFleet Routes
|--------------------------------------------------------------------------
*/

// Redirect from /sharpfleet to /app/sharpfleet
Route::get('/sharpfleet', function () {
    return redirect('/app/sharpfleet');
});

Route::prefix('app/sharpfleet')->group(function () {

    // Admin portal SSO handoff (from /admin)
    Route::get('/sso', [SsoController::class, 'login']);

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
    | Test Home Page
    |--------------------------------------------------------------------------
    */
    Route::get('/test-home', function () {
        return view('sharpfleet.test-home');
    });

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */
    Route::get('/login', [AuthController::class, 'showLogin']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Registration (public - no auth required)
    Route::get('/admin/register', [RegisterController::class, 'showRegistrationForm']);
    Route::post('/admin/register', [RegisterController::class, 'register']);
    Route::get('/register/success', [RegisterController::class, 'showSuccess']);
    Route::get('/activate/{token}', [RegisterController::class, 'activate']);
    Route::post('/activate/complete', [RegisterController::class, 'completeRegistration']);

    // Driver invites (public acceptance)
    Route::get('/invite/{token}', [DriverInviteController::class, 'showAcceptForm']);
    Route::post('/invite/complete', [DriverInviteController::class, 'complete']);

    /*
    |--------------------------------------------------------------------------
    | Admin Routes (ADMIN ONLY)
    |--------------------------------------------------------------------------
    */
    Route::middleware([\App\Http\Middleware\SharpFleetAdminAuth::class, \App\Http\Middleware\SharpFleetTrialCheck::class])
        ->prefix('admin')
        ->group(function () {

            Route::get('/', function (Request $request) {
                $user = $request->session()->get('sharpfleet.user');

                if (!$user || $user['role'] !== 'admin') {
                    abort(403, 'Admin access only');
                }

                $organisationId = (int) $user['organisation_id'];

                $driversCount = DB::connection('sharpfleet')
                    ->table('users')
                    ->where('organisation_id', $organisationId)
                    ->where(function ($q) {
                        $q
                            ->where(function ($qq) {
                                $qq
                                    ->where('role', 'driver')
                                    ->where(function ($q2) {
                                        $q2->whereNull('is_driver')->orWhere('is_driver', 1);
                                    });
                            })
                            ->orWhere(function ($qq) {
                                $qq
                                    ->where('role', 'admin')
                                    ->where('is_driver', 1);
                            });
                    })
                    ->count();

                $vehiclesCount = DB::connection('sharpfleet')
                    ->table('vehicles')
                    ->where('organisation_id', $organisationId)
                    ->count();

                $activeTripsCount = DB::connection('sharpfleet')
                    ->table('trips')
                    ->where('organisation_id', $organisationId)
                    ->whereNotNull('started_at')
                    ->whereNull('ended_at')
                    ->count();

                return view('sharpfleet.admin.dashboard', [
                    'driversCount' => $driversCount,
                    'vehiclesCount' => $vehiclesCount,
                    'activeTripsCount' => $activeTripsCount,
                ]);
            });

            // Company
            Route::get('/company', [CompanyController::class, 'index']);
            Route::get('/company/profile', [CompanyProfileController::class, 'edit']);
            Route::post('/company/profile', [CompanyProfileController::class, 'update']);

            // Safety Checks (definition)
            Route::get('/safety-checks', [CompanySafetyCheckController::class, 'index']);
            Route::post('/safety-checks', [CompanySafetyCheckController::class, 'update']);

            // Vehicles (CRUD)
            Route::get('/vehicles', [VehicleController::class, 'index']);
            Route::get('/vehicles/create', [VehicleController::class, 'create']);
            Route::post('/vehicles', [VehicleController::class, 'store']);
            Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit']);
            Route::post('/vehicles/{vehicle}', [VehicleController::class, 'update']);
            Route::post('/vehicles/{vehicle}/archive', [VehicleController::class, 'archive']);

            // Customers
            Route::get('/customers', [CustomerController::class, 'index']);
            Route::post('/customers', [CustomerController::class, 'store']);

            // Bookings
            Route::get('/bookings', [AdminBookingController::class, 'index']);
            Route::post('/bookings', [AdminBookingController::class, 'store']);
            Route::post('/bookings/{booking}/change-vehicle', [AdminBookingController::class, 'changeVehicle']);
            Route::post('/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel']);
            Route::get('/bookings/available-vehicles', [AdminBookingController::class, 'availableVehicles']);

            // Faults
            Route::get('/faults', [AdminFaultController::class, 'index']);
            Route::post('/faults/{fault}/status', [AdminFaultController::class, 'updateStatus']);

            // Reports
            Route::get('/reports/trips', [ReportController::class, 'trips']);
            Route::get('/reports/vehicles', [ReportController::class, 'vehicles']);

            // Company Settings
            Route::get('/settings', [CompanySettingsController::class, 'edit']);
            Route::post('/settings', [CompanySettingsController::class, 'update']);

            // Users (driver access)
            Route::get('/users', [UserController::class, 'index']);
            Route::get('/users/invite', [AdminDriverInviteController::class, 'create']);
            Route::post('/users/invite', [AdminDriverInviteController::class, 'store']);
            Route::post('/users/{userId}/resend-invite', [AdminDriverInviteController::class, 'resend'])->whereNumber('userId');
            Route::get('/users/{userId}/edit', [UserController::class, 'edit'])->whereNumber('userId');
            Route::post('/users/{userId}', [UserController::class, 'update'])->whereNumber('userId');

            // Trial expired page (no middleware needed)
            Route::get('/trial-expired', function () {
                return view('sharpfleet.admin.trial-expired');
            })->withoutMiddleware([\App\Http\Middleware\SharpFleetTrialCheck::class]);
        });

    /*
    |--------------------------------------------------------------------------
    | Driver Routes (DRIVER ONLY)
    |--------------------------------------------------------------------------
    */
    Route::middleware([\App\Http\Middleware\SharpFleetDriverAuth::class, \App\Http\Middleware\SharpFleetTrialCheck::class])
        ->group(function () {

            Route::get('/driver', fn () => view('sharpfleet.driver.dashboard'));

            // Trips
            Route::post('/trips/start', [TripController::class, 'start']);
            Route::post('/trips/end', [TripController::class, 'end']);
            Route::post('/trips/offline-sync', [TripController::class, 'offlineSync']);

            // Faults
            Route::post('/faults/from-trip', [FaultController::class, 'storeFromTrip']);
            Route::post('/faults/standalone', [FaultController::class, 'storeStandalone']);

            // Bookings
            Route::get('/bookings', [BookingController::class, 'upcoming']);
            Route::post('/bookings', [BookingController::class, 'store']);
            Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
            Route::get('/bookings/available-vehicles', [BookingController::class, 'availableVehicles']);
            Route::post('/bookings/start-trip', [BookingController::class, 'startTrip']);
        });

    /*
    |--------------------------------------------------------------------------
    | Debug (ADMIN ONLY)
    |--------------------------------------------------------------------------
    */
    Route::get('/debug', fn () => view('sharpfleet.debug'))
        ->middleware(\App\Http\Middleware\SharpFleetAdminAuth::class);
});
