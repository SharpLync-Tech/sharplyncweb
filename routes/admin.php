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

Route::get('/admin/signin', fn () => view('admin.auth.login'))->name('admin.signin');
Route::get('/admin/login', [MicrosoftController::class, 'redirectToMicrosoft'])->name('login');
Route::get('/auth/callback', [MicrosoftController::class, 'handleCallback']);
Route::get('/admin/logout', [MicrosoftController::class, 'logout'])->name('logout');

Route::middleware(['web', 'admin.auth'])->prefix('admin')->group(function () {

    /** Dashboard */
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    /** Testimonials */
    Route::get('/testimonials', [TestimonialController::class, 'index'])->name('admin.testimonials.index');
    Route::get('/testimonials/create', [TestimonialController::class, 'create'])->name('admin.testimonials.create');
    Route::post('/testimonials/store', [TestimonialController::class, 'store'])->name('admin.testimonials.store');
    Route::get('/testimonials/{id}/edit', [TestimonialController::class, 'edit'])->name('admin.testimonials.edit');
    Route::put('/testimonials/{id}', [TestimonialController::class, 'update'])->name('admin.testimonials.update');
    Route::delete('/testimonials/{id}', [TestimonialController::class, 'destroy'])->name('admin.testimonials.destroy');

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

});