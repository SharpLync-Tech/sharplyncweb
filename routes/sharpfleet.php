<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\SharpFleet\AuthController;
use App\Http\Controllers\SharpFleet\Auth\ForgotPasswordController as SharpFleetForgotPasswordController;
use App\Http\Controllers\SharpFleet\Auth\ResetPasswordController as SharpFleetResetPasswordController;
use App\Http\Controllers\SharpFleet\HelpController;
use App\Http\Controllers\SharpFleet\SsoController;
use App\Http\Controllers\SharpFleet\TripController;
use App\Http\Controllers\SharpFleet\FaultController;
use App\Http\Controllers\SharpFleet\BookingController;
use App\Http\Controllers\SharpFleet\Admin\RegisterController;
use App\Http\Controllers\SharpFleet\Admin\AccountController;
use App\Http\Controllers\SharpFleet\Admin\BranchController;
use App\Http\Controllers\SharpFleet\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\SharpFleet\Admin\CompanyController;
use App\Http\Controllers\SharpFleet\Admin\CompanyProfileController;
use App\Http\Controllers\SharpFleet\Admin\CompanySafetyCheckController;
use App\Http\Controllers\SharpFleet\Admin\CompanySettingsController;
use App\Http\Controllers\SharpFleet\Admin\CustomerController;
use App\Http\Controllers\SharpFleet\Admin\DashboardController;
use App\Http\Controllers\SharpFleet\Admin\DriverInviteController as AdminDriverInviteController;
use App\Http\Controllers\SharpFleet\Admin\FaultController as AdminFaultController;
use App\Http\Controllers\SharpFleet\Admin\ReminderController;
use App\Http\Controllers\SharpFleet\Admin\ReportController;
use App\Http\Controllers\SharpFleet\Admin\SetupWizardController;
use App\Http\Controllers\SharpFleet\Admin\UserController;
use App\Http\Controllers\SharpFleet\Admin\VehicleController;
use App\Http\Controllers\SharpFleet\Admin\VehicleAiTestController;
use App\Http\Controllers\SharpFleet\DriverInviteController;
use App\Support\SharpFleet\Roles;
use App\Http\Controllers\SharpFleet\DriverMobileController;

/*
|--------------------------------------------------------------------------
| SharpFleet Routes
|--------------------------------------------------------------------------
*/

Route::get('/sharpfleet', fn () => view('sharpfleet.home'));
Route::get('/sharpfleet/about', fn () => view('sharpfleet.about'));
Route::get('/sharpfleet/why', fn () => view('sharpfleet.why'));

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

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */
    Route::get('/login', [AuthController::class, 'showLogin']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::match(['get','post'], '/logout', [AuthController::class, 'logout']);
    Route::get('/admin/login', [AuthController::class, 'showLogin']);
    Route::match(['get','post'], '/admin/logout', [AuthController::class, 'logout']);

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

    Route::get('/admin/trial-expired', fn () => view('sharpfleet.admin.trial-expired'))
        ->middleware(\App\Http\Middleware\SharpFleetAdminAuth::class);

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
        Route::get('/mobile', [DriverMobileController::class, 'dashboard'])
            ->name('sharpfleet.mobile.dashboard');

        Route::get('/mobile/start', [DriverMobileController::class, 'dashboard'])
            ->name('sharpfleet.mobile.start');

        Route::get('/mobile/history', [DriverMobileController::class, 'history'])
            ->name('sharpfleet.mobile.history');

        Route::get('/mobile/bookings', [DriverMobileController::class, 'bookings'])
            ->name('sharpfleet.mobile.bookings');

        Route::get('/mobile/more', [DriverMobileController::class, 'more'])
            ->name('sharpfleet.mobile.more');

        Route::get('/mobile/support', [DriverMobileController::class, 'support'])
            ->name('sharpfleet.mobile.support');
        Route::post('/mobile/support', [DriverMobileController::class, 'supportSend'])
            ->name('sharpfleet.mobile.support.send');

        Route::get('/mobile/help', [DriverMobileController::class, 'help'])
            ->name('sharpfleet.mobile.help');

        Route::get('/mobile/about', [DriverMobileController::class, 'about'])
            ->name('sharpfleet.mobile.about');

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
    |----------------------------------------------------------------------
    | Admin Routes (ADMIN ONLY)
    |----------------------------------------------------------------------
    */
    Route::middleware([
        \App\Http\Middleware\SharpFleetAdminAuth::class,
        \App\Http\Middleware\SharpFleetTrialCheck::class,
        \App\Http\Middleware\SharpFleetAuditLog::class,
    ])->group(function () {

        Route::get('/admin', [DashboardController::class, 'index']);
        Route::get('/admin/dashboard', [DashboardController::class, 'index']);

        Route::get('/admin/account', [AccountController::class, 'show']);
        Route::post('/admin/account/subscribe', [AccountController::class, 'subscribe']);
        Route::post('/admin/account/cancel-trial', [AccountController::class, 'cancelTrial']);
        Route::post('/admin/account/cancel-subscription', [AccountController::class, 'cancelSubscription']);
        Route::post('/admin/account/upgrade-to-sole-trader', [AccountController::class, 'upgradeToSoleTrader']);

        Route::get('/admin/company', [CompanyController::class, 'index']);
        Route::get('/admin/company/profile', [CompanyProfileController::class, 'edit']);
        Route::post('/admin/company/profile', [CompanyProfileController::class, 'update']);
        Route::get('/admin/settings', [CompanySettingsController::class, 'edit']);
        Route::post('/admin/settings', [CompanySettingsController::class, 'update']);
        Route::get('/admin/safety-checks', [CompanySafetyCheckController::class, 'index']);
        Route::post('/admin/safety-checks', [CompanySafetyCheckController::class, 'update']);

        Route::get('/admin/branches', [BranchController::class, 'index']);
        Route::get('/admin/branches/create', [BranchController::class, 'create']);
        Route::post('/admin/branches', [BranchController::class, 'store']);
        Route::get('/admin/branches/{branchId}/edit', [BranchController::class, 'edit'])->whereNumber('branchId');
        Route::post('/admin/branches/{branchId}', [BranchController::class, 'update'])->whereNumber('branchId');

        Route::get('/admin/users/invite', [AdminDriverInviteController::class, 'create']);
        Route::post('/admin/users/invite', [AdminDriverInviteController::class, 'store']);
        Route::get('/admin/users/add', [AdminDriverInviteController::class, 'createManual']);
        Route::post('/admin/users/add', [AdminDriverInviteController::class, 'storeManual']);
        Route::get('/admin/users/import', [AdminDriverInviteController::class, 'createImport']);
        Route::post('/admin/users/import', [AdminDriverInviteController::class, 'storeImport']);
        Route::post('/admin/users/send-invites', [AdminDriverInviteController::class, 'sendInvites']);
        Route::post('/admin/users/{userId}/resend-invite', [AdminDriverInviteController::class, 'resend'])->whereNumber('userId');

        Route::get('/admin/users', [UserController::class, 'index']);
        Route::get('/admin/users/{userId}/details', [UserController::class, 'details'])->whereNumber('userId');
        Route::get('/admin/users/{userId}/edit', [UserController::class, 'edit'])->whereNumber('userId');
        Route::post('/admin/users/{userId}', [UserController::class, 'update'])->whereNumber('userId');
        Route::post('/admin/users/{userId}/delete', [UserController::class, 'destroy'])->whereNumber('userId');
        Route::post('/admin/users/{userId}/unarchive', [UserController::class, 'unarchive'])->whereNumber('userId');

        Route::get('/admin/customers', [CustomerController::class, 'index']);
        Route::get('/admin/customers/create', [CustomerController::class, 'create']);
        Route::post('/admin/customers', [CustomerController::class, 'store']);
        Route::get('/admin/customers/{customerId}/edit', [CustomerController::class, 'edit'])->whereNumber('customerId');
        Route::post('/admin/customers/{customerId}', [CustomerController::class, 'update'])->whereNumber('customerId');
        Route::post('/admin/customers/{customerId}/archive', [CustomerController::class, 'archive'])->whereNumber('customerId');

        Route::get('/admin/vehicles', [VehicleController::class, 'index']);
        Route::get('/admin/vehicles/assigned', [VehicleController::class, 'assigned']);
        Route::get('/admin/vehicles/out-of-service', [VehicleController::class, 'outOfService']);
        Route::get('/admin/vehicles/create', [VehicleController::class, 'create']);
        Route::get('/admin/vehicles/create/confirm', [VehicleController::class, 'confirmCreate']);
        Route::post('/admin/vehicles/create/confirm', [VehicleController::class, 'confirmStore']);
        Route::post('/admin/vehicles/create/cancel', [VehicleController::class, 'cancelCreate']);
        Route::post('/admin/vehicles', [VehicleController::class, 'store']);
        Route::get('/admin/vehicles/{vehicle}/details', [VehicleController::class, 'details']);
        Route::get('/admin/vehicles/{vehicle}/edit', [VehicleController::class, 'edit']);
        Route::post('/admin/vehicles/{vehicle}', [VehicleController::class, 'update']);
        Route::get('/admin/vehicles/{vehicle}/archive/confirm', [VehicleController::class, 'confirmArchive']);
        Route::post('/admin/vehicles/{vehicle}/archive/confirm', [VehicleController::class, 'confirmArchiveStore']);
        Route::post('/admin/vehicles/{vehicle}/archive/cancel', [VehicleController::class, 'cancelArchive']);
        Route::post('/admin/vehicles/{vehicle}/archive', [VehicleController::class, 'archive']);

        Route::get('/admin/vehicles-ai-test', [VehicleAiTestController::class, 'index'])
            ->name('sharpfleet.admin.vehicles-ai-test');
        Route::post('/admin/vehicles-ai-test/makes', [VehicleAiTestController::class, 'makes'])
            ->name('sharpfleet.admin.vehicles-ai-test.makes');
        Route::post('/admin/vehicles-ai-test/models', [VehicleAiTestController::class, 'models'])
            ->name('sharpfleet.admin.vehicles-ai-test.models');
        Route::post('/admin/vehicles-ai-test/trims', [VehicleAiTestController::class, 'trims'])
            ->name('sharpfleet.admin.vehicles-ai-test.trims');
        Route::post('/admin/vehicles-ai-test/type', [VehicleAiTestController::class, 'type'])
            ->name('sharpfleet.admin.vehicles-ai-test.type');
        Route::post('/admin/vehicles-ai-test/countries', [VehicleAiTestController::class, 'countries'])
            ->name('sharpfleet.admin.vehicles-ai-test.countries');

        Route::get('/admin/bookings', [AdminBookingController::class, 'index']);
        Route::get('/admin/bookings/feed', [AdminBookingController::class, 'feed']);
        Route::post('/admin/bookings', [AdminBookingController::class, 'store']);
        Route::post('/admin/bookings/{booking}', [AdminBookingController::class, 'update'])->whereNumber('booking');
        Route::post('/admin/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel'])->whereNumber('booking');
        Route::post('/admin/bookings/{booking}/change-vehicle', [AdminBookingController::class, 'changeVehicle'])->whereNumber('booking');
        Route::get('/admin/bookings/available-vehicles', [AdminBookingController::class, 'availableVehicles']);
        Route::get('/admin/trips/active', [AdminBookingController::class, 'activeTrips']);

        Route::get('/admin/faults', [AdminFaultController::class, 'index']);
        Route::post('/admin/faults/{fault}/status', [AdminFaultController::class, 'updateStatus']);

        Route::get('/admin/reminders', [ReminderController::class, 'index']);
        Route::get('/admin/reminders/vehicles', [ReminderController::class, 'vehicles']);
        Route::get('/admin/reports', fn () => redirect('/app/sharpfleet/admin/reports/trips'));
        Route::get('/admin/reports/trips', [ReportController::class, 'trips']);

        Route::get('/admin/setup/company', [SetupWizardController::class, 'company']);
        Route::post('/admin/setup/company', [SetupWizardController::class, 'storeCompany']);
        Route::get('/admin/setup/settings/presence', [SetupWizardController::class, 'settingsPresence']);
        Route::post('/admin/setup/settings/presence', [SetupWizardController::class, 'storeSettingsPresence']);
        Route::get('/admin/setup/settings/customer', [SetupWizardController::class, 'settingsCustomer']);
        Route::post('/admin/setup/settings/customer', [SetupWizardController::class, 'storeSettingsCustomer']);
        Route::get('/admin/setup/settings/trip-rules', [SetupWizardController::class, 'settingsTripRules']);
        Route::post('/admin/setup/settings/trip-rules', [SetupWizardController::class, 'storeSettingsTripRules']);
        Route::get('/admin/setup/settings/vehicle-tracking', [SetupWizardController::class, 'settingsVehicleTracking']);
        Route::post('/admin/setup/settings/vehicle-tracking', [SetupWizardController::class, 'storeSettingsVehicleTracking']);
        Route::get('/admin/setup/settings/reminders', [SetupWizardController::class, 'settingsReminders']);
        Route::post('/admin/setup/settings/reminders', [SetupWizardController::class, 'storeSettingsReminders']);
        Route::get('/admin/setup/settings/client-addresses', [SetupWizardController::class, 'settingsClientAddresses']);
        Route::post('/admin/setup/settings/client-addresses', [SetupWizardController::class, 'storeSettingsClientAddresses']);
        Route::get('/admin/setup/settings/safety-check', [SetupWizardController::class, 'settingsSafetyCheck']);
        Route::post('/admin/setup/settings/safety-check', [SetupWizardController::class, 'storeSettingsSafetyCheck']);
        Route::get('/admin/setup/settings/incident-reporting', [SetupWizardController::class, 'settingsIncidentReporting']);
        Route::post('/admin/setup/settings/incident-reporting', [SetupWizardController::class, 'storeSettingsIncidentReporting']);
        Route::get('/admin/setup/finish', [SetupWizardController::class, 'finishView']);
        Route::post('/admin/setup/finish', [SetupWizardController::class, 'finish']);
        Route::post('/admin/setup/rerun', [SetupWizardController::class, 'rerun']);

        Route::get('/admin/help', [HelpController::class, 'admin']);
        Route::get('/admin/about', fn () => view('sharpfleet.about'));
    });

    Route::get('/debug', fn () => view('sharpfleet.debug'))
        ->middleware(\App\Http\Middleware\SharpFleetAdminAuth::class);
});
