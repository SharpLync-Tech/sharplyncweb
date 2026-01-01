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
use App\Http\Controllers\SharpFleet\Admin\CompanySafetyCheckController;
use App\Http\Controllers\SharpFleet\Admin\RegisterController;
use App\Http\Controllers\SharpFleet\Admin\UserController;
use App\Http\Controllers\SharpFleet\Admin\DriverInviteController as AdminDriverInviteController;
use App\Http\Controllers\SharpFleet\Admin\ReminderController;
use App\Http\Controllers\SharpFleet\DriverInviteController;
use App\Services\SharpFleet\CompanySettingsService;
use App\Services\SharpFleet\VehicleReminderService;

/*
|--------------------------------------------------------------------------
| SharpFleet Routes
|--------------------------------------------------------------------------
*/

// Public SharpFleet landing page.
// Note: /app/sharpfleet/ may be blocked by nginx (403). Serve the home view directly here.
Route::get('/sharpfleet', function () {
    return view('sharpfleet.home');
});

Route::prefix('app/sharpfleet')
    ->middleware([\App\Http\Middleware\SharpFleetNoStore::class])
    ->group(function () {

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
                'admin'  => response('', 302)->header('Location', '/app/sharpfleet/admin'),
                'driver' => response('', 302)->header('Location', '/app/sharpfleet/driver'),
                default  => response('', 302)->header('Location', '/app/sharpfleet/login'),
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

    // Password reset (public)
    Route::get('/password/forgot', [SharpFleetForgotPasswordController::class, 'showLinkRequestForm']);
    Route::post('/password/email', [SharpFleetForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::get('/password/reset/{token}', [SharpFleetResetPasswordController::class, 'showResetForm']);
    Route::post('/password/reset', [SharpFleetResetPasswordController::class, 'reset']);

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
    Route::middleware([\App\Http\Middleware\SharpFleetAdminAuth::class, \App\Http\Middleware\SharpFleetTrialCheck::class, \App\Http\Middleware\SharpFleetAuditLog::class])
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

                $hasVehicleAssignmentSupport = Schema::connection('sharpfleet')->hasColumn('vehicles', 'assignment_type')
                    && Schema::connection('sharpfleet')->hasColumn('vehicles', 'assigned_driver_id');
                $permanentAssignedVehiclesCount = 0;
                if ($hasVehicleAssignmentSupport) {
                    $permanentAssignedVehiclesCount = (int) DB::connection('sharpfleet')
                        ->table('vehicles')
                        ->where('organisation_id', $organisationId)
                        ->where('is_active', 1)
                        ->where('assignment_type', 'permanent')
                        ->whereNotNull('assigned_driver_id')
                        ->count();
                }

                $hasOutOfServiceSupport = Schema::connection('sharpfleet')->hasColumn('vehicles', 'is_in_service');
                $outOfServiceVehiclesCount = 0;
                if ($hasOutOfServiceSupport) {
                    $outOfServiceVehiclesCount = (int) DB::connection('sharpfleet')
                        ->table('vehicles')
                        ->where('organisation_id', $organisationId)
                        ->where('is_active', 1)
                        ->where('is_in_service', 0)
                        ->count();
                }

                $activeTripsCount = DB::connection('sharpfleet')
                    ->table('trips')
                    ->where('organisation_id', $organisationId)
                    ->whereNotNull('started_at')
                    ->whereNull('ended_at')
                    ->count();

                // Vehicle reminders (dashboard message only; no email scheduling required)
                $settings = new CompanySettingsService($organisationId);
                $regoEnabled = $settings->vehicleRegistrationTrackingEnabled();
                $serviceEnabled = $settings->vehicleServicingTrackingEnabled();

                $vehicleReminders = [
                    'enabled' => ($regoEnabled || $serviceEnabled),
                    'rego_enabled' => $regoEnabled,
                    'service_enabled' => $serviceEnabled,
                    'rego_overdue' => 0,
                    'rego_due_soon' => 0,
                    'service_date_overdue' => 0,
                    'service_date_due_soon' => 0,
                    'service_reading_overdue' => 0,
                    'service_reading_due_soon' => 0,
                ];

                if ($regoEnabled || $serviceEnabled) {
                    $registrationDays = $settings->reminderRegistrationDays();
                    $serviceDays = $settings->reminderServiceDays();
                    $serviceReadingThreshold = $settings->reminderServiceReadingThreshold();

                    $digest = (new VehicleReminderService())->buildDigest(
                        organisationId: $organisationId,
                        registrationDays: $registrationDays,
                        serviceDays: $serviceDays,
                        serviceReadingThreshold: $serviceReadingThreshold,
                        timezone: $settings->timezone()
                    );

                    if ($regoEnabled) {
                        $vehicleReminders['rego_overdue'] = count($digest['registration']['overdue'] ?? []);
                        $vehicleReminders['rego_due_soon'] = count($digest['registration']['due_soon'] ?? []);
                    }
                    if ($serviceEnabled) {
                        $vehicleReminders['service_date_overdue'] = count($digest['serviceDate']['overdue'] ?? []);
                        $vehicleReminders['service_date_due_soon'] = count($digest['serviceDate']['due_soon'] ?? []);
                        $vehicleReminders['service_reading_overdue'] = count($digest['serviceReading']['overdue'] ?? []);
                        $vehicleReminders['service_reading_due_soon'] = count($digest['serviceReading']['due_soon'] ?? []);
                    }
                }

                return view('sharpfleet.admin.dashboard', [
                    'driversCount' => $driversCount,
                    'vehiclesCount' => $vehiclesCount,
                    'hasVehicleAssignmentSupport' => $hasVehicleAssignmentSupport,
                    'permanentAssignedVehiclesCount' => $permanentAssignedVehiclesCount,
                    'activeTripsCount' => $activeTripsCount,
                    'vehicleReminders' => $vehicleReminders,
                    'hasOutOfServiceSupport' => $hasOutOfServiceSupport,
                    'outOfServiceVehiclesCount' => $outOfServiceVehiclesCount,
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

            // Reminders
            Route::get('/reminders', [ReminderController::class, 'index']);

            // Help
            Route::get('/help', [HelpController::class, 'admin']);

            // About
            Route::get('/about', fn () => view('sharpfleet.about'));

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
    Route::middleware([\App\Http\Middleware\SharpFleetDriverAuth::class, \App\Http\Middleware\SharpFleetTrialCheck::class, \App\Http\Middleware\SharpFleetAuditLog::class])
        ->group(function () {

            Route::get('/driver', fn () => view('sharpfleet.driver.dashboard'));

            // Help
            Route::get('/driver/help', [HelpController::class, 'driver']);

            // About
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
