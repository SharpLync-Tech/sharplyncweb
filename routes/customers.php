<?php
/**
 * SharpLync Customer Routes
 * Version: 2.2 (Portal Ecosystem + Secure Downloads)
 * Last updated: 13 Nov 2025 by Max (ChatGPT)
 * 
 * Description:
 *  Unified routing system for the SharpLync Customer environment.
 *  This file governs:
 *   - Registration & onboarding (CRM-linked)
 *   - Authentication (login / logout)
 *   - The secure Customer Portal ecosystem (/portal)
 * 
 *  Highlights:
 *   • Portal now runs via DashboardController (loads $user + $profile)
 *   • Profile routes fully aligned with controller methods
 *   • Secure, logged, and signed TeamViewer download (no public access)
 */

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\Auth\LoginController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\Auth\ForgotPasswordController;
use App\Http\Controllers\Customer\Auth\ResetPasswordController;

// ======================================================
// PART 1 — CUSTOMER REGISTRATION & ONBOARDING
// ======================================================

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

// Password setup after verification
Route::get('/set-password/{id}', [RegisterController::class, 'showPasswordForm'])->name('password.create');
Route::post('/set-password/{id}', [RegisterController::class, 'savePassword'])->name('password.store');

// Onboarding (CRM-linked profile setup)
Route::get('/customer/setup-profile', [ProfileController::class, 'create'])->name('profile.create');
Route::post('/customer/setup-profile', [ProfileController::class, 'store'])->name('profile.store');

// Edit & Update existing customer profile
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('customer.profile.edit');
Route::post('/profile/update', [ProfileController::class, 'update'])->name('customer.profile.update');

// Onboarding completion screen
Route::get('/customer/onboard-complete', fn() => view('customers.onboard-complete'))->name('onboard.complete');

// Legacy onboarding test (safe to remove later)
Route::get('/onboard', [CustomerController::class, 'create'])->name('customers.create');
Route::post('/onboard', [CustomerController::class, 'store'])->name('customers.store');

// Standalone portal test routes
Route::get('/portal-standalone', fn() => view('customers.portal-standalone'))
    ->name('customer.portal.standalone')
    ->middleware('auth:customer');
Route::get('/portal_test', fn() => view('customers.portal_test'))->name('customer.portal_test');


// ======================================================
// PART 2 — AUTHENTICATION (LOGIN / LOGOUT)
// ======================================================

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('customer.login');
Route::post('/login', [LoginController::class, 'login'])->name('customer.login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('customer.logout');


// ========================================
// CUSTOMER PASSWORD RESET
// ========================================


Route::get('/password/forgot', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->name('customer.password.request');

Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('customer.password.email');

Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->name('customer.password.reset.form');


Route::post('/password/reset', [ResetPasswordController::class, 'reset'])
    ->name('customer.password.update');



// ======================================================
// PART 3 — CUSTOMER PORTAL (SECURE ECOSYSTEM)
// ======================================================

Route::middleware(['auth:customer'])->group(function () {

    // ===== Main Portal Landing =====
    Route::get('/portal', [DashboardController::class, 'index'])->name('customer.portal');

    // ===== Core Sections =====
    Route::get('/portal/security', fn() => view('customers.security'))->name('customer.security');
    Route::get('/portal/support', fn() => view('customers.support'))->name('customer.support');
    Route::get('/portal/account', fn() => view('customers.account'))->name('customer.account');
    Route::get('/portal/billing', fn() => view('customers.billing'))->name('customer.billing');
    Route::get('/portal/documents', fn() => view('customers.documents'))->name('customer.documents');

    // ======================================================
    // PART 4 — FILE DOWNLOADS (SECURE + LOGGED)
    // ======================================================
    Route::get('/portal/teamviewer-download', function () {
        $user = Auth::user();
        $file = storage_path('app/secure_downloads/SharpLync_QuickSupport.exe');

        if (!file_exists($file)) {
            abort(404, 'Quick Support tool not found.');
        }

        // Log download for auditing
        Log::info('TeamViewer downloaded by: ' . ($user->email ?? 'unknown') . ' (ID ' . ($user->id ?? '-') . ')');

        // Signed link validation (optional but enabled here)
        if (!request()->hasValidSignature()) {
            abort(403, 'Invalid or expired download link.');
        }

        return response()->download($file, 'SharpLync_QuickSupport.exe', [
            'Content-Type' => 'application/octet-stream',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    })->middleware('auth:customer')->name('customer.teamviewer.download');
});
