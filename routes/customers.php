<?php
/**
 * SharpLync Customer Routes
 * Version: 2.4 (Portal Ecosystem + Avatar Upload + Login-time 2FA)
 * Updated: 26 Nov 2025 by Max (ChatGPT)
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
use App\Http\Controllers\Customer\SecurityController;


// ======================================================
// PART 1 — CUSTOMER REGISTRATION & ONBOARDING
// ======================================================

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

// Password setup after verification
Route::get('/set-password/{id}', [RegisterController::class, 'showPasswordForm'])->name('password.create');
Route::post('/set-password/{id}', [RegisterController::class, 'savePassword'])->name('password.store');

// CRM-linked profile onboarding
Route::get('/customer/setup-profile', [ProfileController::class, 'create'])->name('profile.create');
Route::post('/customer/setup-profile', [ProfileController::class, 'store'])->name('profile.store');

// Edit & Update customer profile
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('customer.profile.edit');
Route::post('/profile/update', [ProfileController::class, 'update'])->name('customer.profile.update');

// Onboarding completion
Route::get('/customer/onboard-complete', fn() => view('customers.onboard-complete'))->name('onboard.complete');


// ======================================================
// PART 2 — AUTHENTICATION (LOGIN / LOGOUT)
// ======================================================

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('customer.login');
Route::post('/login', [LoginController::class, 'login'])->name('customer.login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('customer.logout');


// ======================================================
// PART 2B — LOGIN-TIME 2FA (NO LOGIN REQUIRED)
// ======================================================

Route::middleware('web')
    ->prefix('portal/security')
    ->name('customer.security.')
    ->group(function () {

        Route::post('/email/send-login-code', [SecurityController::class, 'sendLogin2FACode'])
            ->name('email.send-login-code');

        Route::post('/email/verify-login-code', [SecurityController::class, 'verifyLogin2FACode'])
            ->name('email.verify-login-code');
    });


// ======================================================
// CUSTOMER PASSWORD RESET
// ======================================================

Route::get('/password/forgot', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->name('customer.password.request');

Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('customer.password.email');

Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->name('customer.password.reset.form');

Route::post('/password/reset', [ResetPasswordController::class, 'reset'])
    ->name('customer.password.update');


// ======================================================
// PART 3 — CUSTOMER PORTAL (AUTH REQUIRED)
// ======================================================

Route::middleware(['auth:customer'])->group(function () {

    Route::get('/portal', [DashboardController::class, 'index'])->name('customer.portal');

    Route::get('/portal/security', fn() => view('customers.security'))->name('customer.security');
    Route::get('/portal/support', fn() => view('customers.support'))->name('customer.support');
    Route::get('/portal/account', fn() => view('customers.account'))->name('customer.account');
    Route::get('/portal/billing', fn() => view('customers.billing'))->name('customer.billing');
    Route::get('/portal/documents', fn() => view('customers.documents'))->name('customer.documents');


    // ======================================================
    // PART 3A — PROFILE PHOTO ACTIONS (NEW)
    // ======================================================

    Route::post('/profile/update-photo',
        [ProfileController::class, 'updatePhoto']
    )->name('customer.profile.update-photo');

    Route::post('/profile/remove-photo',
        [ProfileController::class, 'removePhoto']
    )->name('customer.profile.remove-photo');


    // ======================================================
        // PART 4 — REMOTE SUPPORT (UI PAGE + DOWNLOAD)
        // ======================================================

        // Remote Support UI page (PRETTY UI)
        Route::get('/portal/remote-support', function () {
            return view('customers.portal.remote-support');
        })->name('customer.remote-support');


        // Logged-in customers — direct download (NO signature needed)
        Route::get('/portal/teamviewer-download', function () {

            $user = Auth::user();
            $file = base_path('app/secure_downloads/SharpLync_QuickSupport.exe');

            if (!file_exists($file)) {
                Log::warning('Remote support download attempted but file missing.');
                abort(404, 'Remote support tool not available.');
            }

            Log::info('TeamViewer downloaded by ' . ($user->email ?? 'Unknown User'));

            return response()->download($file, 'SharpLync_QuickSupport.exe');
        })->name('customer.teamviewer.download');



        // =====================================================================
        // Ad-hoc Signed Download (NO login required, signature required)
        // =====================================================================

        Route::get('/portal/teamviewer-download/signed', function () {

            if (!request()->hasValidSignature()) {
                abort(403, 'Invalid or expired link.');
            }

            $file = base_path('app/secure_downloads/SharpLync_QuickSupport.exe');

            if (!file_exists($file)) {
                Log::warning('SIGNED remote support download attempted but file missing.');
                abort(404, 'Remote support tool not available.');
            }

            Log::info('SIGNED TeamViewer link downloaded (ad-hoc user)');

            return response()->download($file, 'SharpLync_QuickSupport.exe');
        })->name('customer.teamviewer.download.signed');


});
