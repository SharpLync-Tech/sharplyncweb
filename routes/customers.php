<?php
/**
 * SharpLync Customer Routes
 * Version: 2.0 (Portal Integration)
 * Last updated: 12 Nov 2025 by Max (ChatGPT)
 * 
 * Description:
 *  Fully modular routing system for the SharpLync Customer environment.
 *  - Part 1: Registration, onboarding, and profile management
 *  - Part 2: Isolated Customer Portal ecosystem (/portal)
 * 
 *  The portal now operates independently of the main site and other modules.
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\Auth\LoginController;

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

// Edit existing customer profile
Route::get('/profile/edit', [ProfileController::class, 'edit'])
    ->name('profile.edit');
Route::post('/profile/update', [ProfileController::class, 'update'])
    ->name('profile.update');

// Onboarding complete screen
Route::get('/customer/onboard-complete', function () {
    return view('customers.onboard-complete');
})->name('onboard.complete');

// Temporary testing route (legacy)
Route::get('/onboard', [CustomerController::class, 'create'])
    ->name('customers.create');
Route::post('/onboard', [CustomerController::class, 'store'])
    ->name('customers.store');

// routes/customer.php (add this one line just to test safely)
Route::get('/portal-standalone', fn() => view('customers.portal-standalone'))
    ->name('customer.portal.standalone')
    ->middleware('auth:customer');
// Test
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
// All routes within this section are authenticated and 
// belong exclusively to the /portal environment.
// The portal uses its own layout, stylesheet, and views 
// under /resources/views/customers/*

Route::middleware(['auth:customer'])->group(function () {

    // ===== Main Portal Landing =====
    Route::get('/portal', fn() => view('customers.portal'))
        ->name('customer.portal');

    // ===== Subsections (future-proof placeholders) =====
    Route::get('/portal/billing', fn() => view('customers.billing'))
        ->name('customer.billing');

    Route::get('/portal/security', fn() => view('customers.security'))
        ->name('customer.security');

    Route::get('/portal/support', fn() => view('customers.support'))
        ->name('customer.support');

    // Optional additional areas
    Route::get('/portal/documents', fn() => view('customers.documents'))
        ->name('customer.documents');
});