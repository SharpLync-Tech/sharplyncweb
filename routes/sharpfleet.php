<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Http\Controllers\SharpFleet\AuthController;
use App\Http\Controllers\SharpFleet\Auth\ForgotPasswordController as SharpFleetForgotPasswordController;
use App\Http\Controllers\SharpFleet\Auth\ResetPasswordController as SharpFleetResetPasswordController;
use App\Http\Controllers\SharpFleet\HelpController;
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
use App\Http\Controllers\SharpFleet\Admin\BranchController;
use App\Http\Controllers\SharpFleet\Admin\CompanySafetyCheckController;
use App\Http\Controllers\SharpFleet\Admin\RegisterController;
use App\Http\Controllers\SharpFleet\Admin\UserController;
use App\Http\Controllers\SharpFleet\Admin\DriverInviteController as AdminDriverInviteController;
use App\Http\Controllers\SharpFleet\Admin\SetupWizardController;
use App\Http\Controllers\SharpFleet\Admin\ReminderController;
use App\Http\Controllers\SharpFleet\Admin\AccountController;
use App\Http\Controllers\SharpFleet\DriverInviteController;
use App\Services\SharpFleet\CompanySettingsService;
use App\Services\SharpFleet\BillingDisplayService;
use App\Services\SharpFleet\VehicleReminderService;
use App\Support\SharpFleet\Roles;

/*
|--------------------------------------------------------------------------
| SharpFleet Routes
|--------------------------------------------------------------------------
*/

Route::get('/sharpfleet', fn () => view('sharpfleet.home'));
Route::get('/sharpfleet/about', fn () => view('sharpfleet.about'));

Route::prefix('app/sharpfleet')
    ->middleware([\App\Http\Middleware\SharpFleetNoStore::class])
    ->group(function () {

    Route::get('/sso', [SsoController::class, 'login']);

    Route::get('/', function (Request $request) {
        if ($request->session()->has('sharpfleet.user')) {
            $role = Roles::normalize((string) $request->session()->get('sharpfleet.user.role'));

            return match ($role) {
                Roles::COMPANY_ADMIN,
                Roles::BRANCH_ADMIN,
                Roles::BOOKING_ADMIN => redirect('/app/sharpfleet/admin'),
                Roles::DRIVER => redirect('/app/sharpfleet/driver'),
                default => redirect('/app/sharpfleet/login'),
            };
        }

        return view('sharpfleet.home');
    });

    Route::get('/test-home', fn () => view('sharpfleet.test-home'));

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */
    Route::get('/login', [AuthController::class, 'showLogin']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::match(['get','post'], '/logout', [AuthController::class, 'logout']);

    Route::get('/password/forgot', [SharpFleetForgotPasswordController::class, 'showLinkRequestForm']);
    Route::post('/password/email', [SharpFleetForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::get('/password/reset/{token}', [SharpFleetResetPasswordController::class, 'showResetForm']);
    Route::post('/password/reset', [SharpFleetResetPasswordController::class, 'reset']);

    Route::get('/admin/register', [RegisterController::class, 'showRegistrationForm']);
    Route::post('/admin/register', [RegisterController::class, 'register']);
    Route::get('/register/success', [RegisterController::class, 'showSuccess']);
    Route::get('/activate/{token}', [RegisterController::class, 'activate']);
    Route::post('/activate/complete', [RegisterController::class, 'completeRegistration']);

    Route::get('/invite/{token}', [DriverInviteController::class, 'showAcceptForm']);
    Route::post('/invite/complete', [DriverInviteController::class, 'complete']);

    /*
    |--------------------------------------------------------------------------
    | Driver Routes (DRIVER ONLY)
    |--------------------------------------------------------------------------
    */
    Route::middleware([
        \App\Http\Middleware\SharpFleetDriverAuth::class,
        \App\Http\Middleware\SharpFleetTrialCheck::class,
        \App\Http\Middleware\SharpFleetAuditLog::class,
    ])->group(function () {

        // Desktop driver
        Route::get('/driver', fn () => view('sharpfleet.driver.dashboard'));

        /*
        |--------------------------------------------------------------------------
        | Driver Mobile (PWA)
        |--------------------------------------------------------------------------
        */
        Route::get('/mobile', fn () => view('sharpfleet.mobile.dashboard'))
            ->name('sharpfleet.mobile.dashboard');

        Route::get('/mobile/start', fn () => view('sharpfleet.mobile.dashboard'))
            ->name('sharpfleet.mobile.start');

        Route::get('/mobile/history', fn () => view('sharpfleet.mobile.dashboard'))
            ->name('sharpfleet.mobile.history');

        Route::get('/mobile/more', fn () => view('sharpfleet.mobile.more'))
            ->name('sharpfleet.mobile.more');

        // Driver help / about
        Route::get('/driver/help', [HelpController::class, 'driver']);
        Route::get('/driver/about', fn () => view('sharpfleet.about'));

        // Trips
        Route::post('/trips/start', [TripController::class, 'start']);
        Route::post('/trips/end', [TripController::class, 'end']);
        Route::post('/trips/offline-sync', [TripController::class, 'offlineSync']);
        Route::get('/trips/last-reading', [TripController::class, 'lastReading']);

        // Faults
        Route::post('/faults/from-trip', [FaultController::class, 'storeFromTrip']);
        Route::post('/faults/standalone', [FaultController::class, 'storeStandalone']);

        // Bookings
        Route::get('/bookings', [BookingController::class, 'upcoming']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::post('/bookings/{booking}', [BookingController::class, 'update'])->whereNumber('booking');
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
