<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\Auth\MicrosoftController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\LogViewerController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\DeviceAuditController;
use App\Http\Controllers\Admin\SmsController;
use App\Http\Controllers\Admin\PulseFeedController;
use App\Http\Controllers\Admin\ComponentController;
use App\Http\Controllers\Admin\Support\SupportTicketController;
use App\Http\Controllers\Admin\Support\AdminTicketController;
use App\Http\Controllers\Admin\PortalController;
use App\Http\Controllers\Admin\SharpFleet\PlatformController as SharpFleetPlatformController;

/*
|--------------------------------------------------------------------------
| Authentication (Outside admin middleware)
|--------------------------------------------------------------------------
*/
Route::get('/admin/signin', fn () => view('admin.auth.login'))->name('admin.signin');
Route::get('/admin/login', [MicrosoftController::class, 'redirectToMicrosoft'])->name('login');
Route::get('/auth/callback', [MicrosoftController::class, 'handleCallback']);
Route::get('/admin/logout', [MicrosoftController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Admin Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'admin.auth'])->prefix('admin')->group(function () {

    // Admin landing / product selector
    Route::get('/', fn () => redirect()->route('admin.portal'));
    Route::get('/portal', [PortalController::class, 'index'])->name('admin.portal');
    // Legacy: tenant SharpFleet admin (customer portal) SSO handoff
    Route::get('/sharpfleet/product', [PortalController::class, 'sharpfleet'])->name('admin.sharpfleet.product');

    // SharpFleet platform admin (internal)
    Route::prefix('sharpfleet')->group(function () {
        Route::get('/', [SharpFleetPlatformController::class, 'index'])->name('admin.sharpfleet.platform');
        Route::get('/organisations/{organisationId}', [SharpFleetPlatformController::class, 'organisation'])
            ->whereNumber('organisationId')
            ->name('admin.sharpfleet.organisations.show');
        Route::get('/organisations/{organisationId}/users', [SharpFleetPlatformController::class, 'organisationUsers'])
            ->whereNumber('organisationId')
            ->name('admin.sharpfleet.organisations.users');
        Route::get('/organisations/{organisationId}/vehicles', [SharpFleetPlatformController::class, 'organisationVehicles'])
            ->whereNumber('organisationId')
            ->name('admin.sharpfleet.organisations.vehicles');
        Route::get('/vehicles/{vehicleId}', [SharpFleetPlatformController::class, 'vehicle'])
            ->whereNumber('vehicleId')
            ->name('admin.sharpfleet.vehicles.show');
    });

    /** Dashboard + Settings */
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/settings', fn () => view('admin.settings.index'))->name('admin.settings.index');

    /*
    |--------------------------------------------------------------------------
    | Support → Verification SMS + Tickets
    |--------------------------------------------------------------------------
    */
    Route::prefix('support')->group(function () {

        // Verification SMS
        Route::get('/sms',        [SmsController::class, 'index'])->name('admin.support.sms.index');
        Route::post('/sms/send',  [SmsController::class, 'send'])->name('admin.support.sms.send');
        Route::get('/sms/logs',   [SmsController::class, 'logs'])->name('admin.support.sms.logs');

        // General SMS
        Route::get('/general-sms',        [SmsController::class, 'general'])->name('admin.support.sms.general');
        Route::post('/general-sms/send',  [SmsController::class, 'sendGeneral'])->name('admin.support.sms.general.send');

        // Search recipients
        Route::get('/search-recipients',  [SmsController::class, 'searchRecipients'])
             ->name('admin.support.search');


       /*
        |--------------------------------------------------------------------------
        | Support Tickets (Admin)
        |--------------------------------------------------------------------------
        */
        Route::prefix('tickets')->group(function () {
            Route::get('/', [AdminTicketController::class, 'index'])
                ->name('admin.support.tickets.index');

            Route::get('/{ticket}', [AdminTicketController::class, 'show'])
                ->name('admin.support.tickets.show');

            Route::patch('/{ticket}/status', [AdminTicketController::class, 'updateStatus'])
                ->name('admin.support.tickets.update-status');

            Route::patch('/{ticket}/priority', [AdminTicketController::class, 'updatePriority'])
                ->name('admin.support.tickets.update-priority');

            Route::patch('/{ticket}/quick-resolve', [AdminTicketController::class, 'quickResolve'])
                ->name('admin.support.tickets.quick-resolve');

            Route::post('/{ticket}/reply', [AdminTicketController::class, 'reply'])
                ->name('admin.support.tickets.reply');

            Route::post('/{ticket}/internal-notes', [AdminTicketController::class, 'storeInternalNote'])
                ->name('admin.support.tickets.internal-notes.store');
        });
    });


    /*
    |--------------------------------------------------------------------------
    | Testimonials
    |--------------------------------------------------------------------------
    */
    Route::get('/testimonials',                [TestimonialController::class, 'index'])->name('admin.testimonials.index');
    Route::get('/testimonials/create',         [TestimonialController::class, 'create'])->name('admin.testimonials.create');
    Route::post('/testimonials/store',         [TestimonialController::class, 'store'])->name('admin.testimonials.store');
    Route::get('/testimonials/{id}/edit',      [TestimonialController::class, 'edit'])->name('admin.testimonials.edit');
    Route::put('/testimonials/{id}',           [TestimonialController::class, 'update'])->name('admin.testimonials.update');
    Route::delete('/testimonials/{id}',        [TestimonialController::class, 'destroy'])->name('admin.testimonials.destroy');

    /*
    |--------------------------------------------------------------------------
    | Customers
    |--------------------------------------------------------------------------
    */
    Route::get('/customers',                       [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('admin.customers.index');
    Route::get('/customers/{id}',                 [\App\Http\Controllers\Admin\CustomerController::class, 'show'])->whereNumber('id')->name('admin.customers.show');
    Route::get('/customers/{id}/edit',            [\App\Http\Controllers\Admin\CustomerController::class, 'edit'])->whereNumber('id')->name('admin.customers.edit');
    Route::put('/customers/{id}',                 [\App\Http\Controllers\Admin\CustomerController::class, 'update'])->whereNumber('id')->name('admin.customers.update');

    Route::post('/customers/{id}/send-reset',     [\App\Http\Controllers\Admin\CustomerController::class, 'sendReset'])
        ->whereNumber('id')->name('admin.customers.sendReset');

    /*
    |--------------------------------------------------------------------------
    | Devices
    |--------------------------------------------------------------------------
    */
    Route::prefix('devices')->group(function () {

        Route::get('/',                [DeviceController::class, 'index'])->name('admin.devices.index');
        Route::get('/unassigned',      [DeviceController::class, 'unassigned'])->name('admin.devices.unassigned');

        Route::get('/import',          [DeviceController::class, 'importForm'])->name('admin.devices.import');
        Route::post('/import',         [DeviceController::class, 'importProcess'])->name('admin.devices.import.process');

        Route::get('/{device}/audits',           [DeviceAuditController::class, 'index'])->name('admin.devices.audits.index');
        Route::get('/{device}/audits/{audit}',   [DeviceAuditController::class, 'show'])->name('admin.devices.audits.show');

        Route::post('/{device}/assign',          [DeviceController::class, 'assign'])->name('admin.devices.assign');
        Route::delete('/{device}/delete',        [DeviceController::class, 'destroy'])->name('admin.devices.destroy');

        Route::get('/{device}',                  [DeviceController::class, 'show'])->name('admin.devices.show'); // MUST BE LAST
    });

    /*
    |--------------------------------------------------------------------------
    | Pulse Feed
    |--------------------------------------------------------------------------
    */
    Route::get('/pulse',                    [PulseFeedController::class, 'index'])->name('admin.pulse.index');
    Route::get('/pulse/create',             [PulseFeedController::class, 'create'])->name('admin.pulse.create');
    Route::post('/pulse/store',             [PulseFeedController::class, 'store'])->name('admin.pulse.store');
    Route::get('/pulse/{pulse}/edit',       [PulseFeedController::class, 'edit'])->name('admin.pulse.edit');
    Route::put('/pulse/{pulse}',            [PulseFeedController::class, 'update'])->name('admin.pulse.update');
    Route::delete('/pulse/{pulse}',         [PulseFeedController::class, 'destroy'])->name('admin.pulse.destroy');

    /*
    |--------------------------------------------------------------------------
    | Components
    |--------------------------------------------------------------------------
    */
    Route::get('/components',                  [ComponentController::class, 'index'])->name('admin.components.index');
    Route::get('/components/create',           [ComponentController::class, 'create'])->name('admin.components.create');
    Route::post('/components/store',           [ComponentController::class, 'store'])->name('admin.components.store');
    Route::get('/components/{component}/edit', [ComponentController::class, 'edit'])->name('admin.components.edit');
    Route::put('/components/{component}',      [ComponentController::class, 'update'])->name('admin.components.update');
    Route::delete('/components/{component}',   [ComponentController::class, 'destroy'])->name('admin.components.destroy');

});
