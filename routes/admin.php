<?php
/**
 * SharpLync Admin Routes
 * Version: 1.4
 * Last updated: 17 Nov 2025 by Max (ChatGPT)
 *
 * Changes:
 * - Corrected route order inside /admin/devices prefix.
 * - Moved dynamic /{device} route to bottom to prevent swallowing /import.
 * - Ensured Device Import routes resolve correctly at /admin/devices/import.
 */

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

Route::get('/admin/signin', fn () => view('admin.auth.login'))->name('admin.signin');
Route::get('/admin/login', [MicrosoftController::class, 'redirectToMicrosoft'])->name('login');
Route::get('/auth/callback', [MicrosoftController::class, 'handleCallback']);
Route::get('/admin/logout', [MicrosoftController::class, 'logout'])->name('logout');

Route::middleware(['web', 'admin.auth'])->prefix('admin')->group(function () {

    /** Dashboard */
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/settings', function () {
        return view('admin.settings.index');
    })->name('admin.settings.index');

    /** Support → Verification SMS */
    Route::prefix('support')->group(function () {

        // SMS form page
        Route::get('/sms', [SmsController::class, 'index'])
            ->name('admin.support.sms.index');

        // Send SMS action
        Route::post('/sms/send', [SmsController::class, 'send'])
            ->name('admin.support.sms.send');

        // SMS Logs
        Route::get('/sms/logs', [SmsController::class, 'logs'])
            ->name('admin.support.sms.logs');
    });

    /** Testimonials */
    Route::get('/testimonials', [TestimonialController::class, 'index'])->name('admin.testimonials.index');
    Route::get('/testimonials/create', [TestimonialController::class, 'create'])->name('admin.testimonials.create');
    Route::post('/testimonials/store', [TestimonialController::class, 'store'])->name('admin.testimonials.store');
    Route::get('/testimonials/{id}/edit', [TestimonialController::class, 'edit'])->name('admin.testimonials.edit');
    Route::put('/testimonials/{id}', [TestimonialController::class, 'update'])->name('admin.testimonials.update');
    Route::delete('/testimonials/{id}', [TestimonialController::class, 'destroy'])->name('admin.testimonials.destroy');

    /** Customers */
    Route::get('/customers', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])
        ->name('admin.customers.index');

    Route::get('/customers/{id}', [\App\Http\Controllers\Admin\CustomerController::class, 'show'])
        ->whereNumber('id')->name('admin.customers.show');

    Route::get('/customers/{id}/edit', [\App\Http\Controllers\Admin\CustomerController::class, 'edit'])
        ->whereNumber('id')->name('admin.customers.edit');

    Route::put('/customers/{id}', [\App\Http\Controllers\Admin\CustomerController::class, 'update'])
        ->whereNumber('id')->name('admin.customers.update');

    // Send password reset email for a customer
    Route::post('/customers/{id}/send-reset', [\App\Http\Controllers\Admin\CustomerController::class, 'sendReset'])
        ->whereNumber('id')
        ->name('admin.customers.sendReset');

    /** Devices */
    Route::prefix('devices')->group(function () {

        // Listing routes
        Route::get('/', [DeviceController::class, 'index'])->name('admin.devices.index');
        Route::get('/unassigned', [DeviceController::class, 'unassigned'])->name('admin.devices.unassigned');

        // Import routes (must be BEFORE dynamic route)
        Route::get('/import', [DeviceController::class, 'importForm'])->name('admin.devices.import');
        Route::post('/import', [DeviceController::class, 'importProcess'])->name('admin.devices.import.process');

        // Audit routes
        Route::get('/{device}/audits', [DeviceAuditController::class, 'index'])->name('admin.devices.audits.index');
        Route::get('/{device}/audits/{audit}', [DeviceAuditController::class, 'show'])->name('admin.devices.audits.show');

        // Assignment + delete
        Route::post('/{device}/assign', [DeviceController::class, 'assign'])->name('admin.devices.assign');
        Route::delete('/{device}/delete', [DeviceController::class, 'destroy'])->name('admin.devices.destroy');

        // ⚠ MUST COME LAST — or it will swallow all routes like /import
        Route::get('/{device}', [DeviceController::class, 'show'])->name('admin.devices.show');
    });

    /** Pulse Feed */
    Route::get('/pulse', [PulseFeedController::class, 'index'])->name('admin.pulse.index');
    Route::get('/pulse/create', [PulseFeedController::class, 'create'])->name('admin.pulse.create');
    Route::post('/pulse/store', [PulseFeedController::class, 'store'])->name('admin.pulse.store');
    Route::get('/pulse/{pulse}/edit', [PulseFeedController::class, 'edit'])->name('admin.pulse.edit');
    Route::put('/pulse/{pulse}', [PulseFeedController::class, 'update'])->name('admin.pulse.update');
    Route::delete('/pulse/{pulse}', [PulseFeedController::class, 'destroy'])->name('admin.pulse.destroy');

    /** Components */
    Route::get('/components', [ComponentController::class, 'index'])->name('admin.components.index');
    Route::get('/components/create', [ComponentController::class, 'create'])->name('admin.components.create');
    Route::post('/components/store', [ComponentController::class, 'store'])->name('admin.components.store');
    Route::get('/components/{component}/edit', [ComponentController::class, 'edit'])->name('admin.components.edit');
    Route::put('/components/{component}', [ComponentController::class, 'update'])->name('admin.components.update');
    Route::delete('/components/{component}', [ComponentController::class, 'destroy'])->name('admin.components.destroy');
});
