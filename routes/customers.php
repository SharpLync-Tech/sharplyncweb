<?php
/**
 * SharpLync Customer Routes
 * Version: 2.1 (Portal Ecosystem + Download Integration)
 * Last updated: 13 Nov 2025 by Max (ChatGPT)
 * 
 * Description:
 *  Unified routing system for the SharpLync Customer environment.
 *  This file governs:
 *   - Registration & onboarding (linked to CRM)
 *   - Authentication (login / logout)
 *   - The standalone Customer Portal ecosystem (/portal)
 * 
 *  Notes:
 *   • The portal now loads via DashboardController for dynamic data ($user, $profile)
 *   • Support & Security views exist as modular placeholders
 *   • Includes secure TeamViewer Quick Support download endpoint
 *   • Future-ready structure for billing, documents, and expansions
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\Auth\LoginController;
use App\Http\Controllers\Customer\DashboardController;

// ======================================================
// PART 1 — CUSTOMER REGISTRATION & ONBOARDING
// ======================================================

// Registration
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])
    ->name('register');
Route::post('/register', [RegisterController::class, 'register'])
    ->name('register.submit');

// Password setup after verification
Route::get('/set-password/{id}', [RegisterController::class, 'showPasswordForm'])
    ->name('password.create');
Route::post('/set-password/{id}', [RegisterController::class, 'savePassword'])
    ->name('password.store');

// Onboarding (CRM-linked profile setup)
Route::get('/customer/setup-profile', [ProfileController::class, 'create'])
    ->name('profile.create');
Route::post('/customer/setup-profile', [ProfileController::class, 'store'])
    ->name('profile.store');

// Edit existing customer profile (after onboarding)
Route::get('/profile/edit', [ProfileController::class, 'edit'])
    ->name('customer.profile.edit');
Route::post('/profile/update', [ProfileController::class, 'update'])
    ->name('customer.profile.update');

// Onboarding completion screen
Route::get('/customer/onboard-complete', function () {
    return view('customers.onboard-complete');
})->name('onboard.complete');

// Legacy test routes (safe to remove later)
Route::get('/onboard', [CustomerController::class, 'create'])
    ->name('customers.create');
Route::post('/onboard', [CustomerController::class, 'store'])
    ->name('customers.store');

// Temporary standalone / testing variants
Route::get('/portal-standalone', fn() => view('customers.portal-standalone'))
    ->name('customer.portal.standalone')
    ->middleware('auth:customer');

Route::get('/portal_test', fn() => view('customers.portal_test'))
    ->name('customer.portal_test');


// ======================================================
// PART 2 — AUTHENTICATION (LOGIN / LOGOUT)
// ======================================================

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('customer.login');
Route::post('/login', [LoginController::class, 'login'])
    ->name('customer.login.submit');
Route::post('/logout', [LoginController::class, 'logout'])
    ->name('customer.logout');


// ======================================================
// PART 3 — CUSTOMER PORTAL (ISOLATED ECOSYSTEM)
// ======================================================
// All routes below require authentication via the "customer" guard.
// The portal uses its own layout, stylesheet, and modular view files
// located under /resources/views/customers/*

Route::middleware(['auth:customer'])->group(function () {

    // ===== Main Portal Landing =====
    Route::get('/portal', [DashboardController::class, 'index'])
        ->name('customer.portal');

    // ===== Portal Sections =====
    Route::get('/portal/security', fn() => view('customers.security'))
        ->name('customer.security');

    Route::get('/portal/support', fn() => view('customers.support'))
        ->name('customer.support');

    Route::get('/portal/account', fn() => view('customers.account'))
        ->name('customer.account');

    Route::get('/portal/billing', fn() => view('customers.billing'))
        ->name('customer.billing');

    Route::get('/portal/documents', fn() => view('customers.documents'))
        ->name('customer.documents');

    // ======================================================
    // PART 4 — FILE DOWNLOADS & TOOLS
    // ======================================================
    // TeamViewer Quick Support executable download.
    // Adjust file path if stored elsewhere under /public/downloads
    Route::get('/portal/teamviewer-download', function () {
        $file = public_path('downloads/SharpLync_QuickSupport.exe');

        if (file_exists($file)) {
            return response()->download($file);
        }

        abort(404, 'Quick Support tool not found.');
    })->name('customer.teamviewer.download');
});
