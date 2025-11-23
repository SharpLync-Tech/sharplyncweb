<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\SecurityController;

/**
 * ======================================================
 *  PORTAL 2FA ROUTES (USER IS LOGGED IN)
 * ======================================================
 */
Route::middleware(['auth:customer'])
    ->prefix('portal/security')
    ->name('customer.security.')
    ->group(function () {

        // (For later if you add toggle logic)
        Route::post('/2fa/email/toggle', [SecurityController::class, 'toggleEmail'])
            ->name('2fa.email.toggle');

        // Enable Email 2FA – send verification code (from portal modal)
        Route::post('/email/send-code', [SecurityController::class, 'sendEmail2FACode'])
            ->name('email.send-code');

        // Enable Email 2FA – verify code (from portal modal)
        Route::post('/email/verify-code', [SecurityController::class, 'verifyEmail2FACode'])
            ->name('email.verify-code');
    });

/**
 * ======================================================
 *  LOGIN-TIME 2FA ROUTES (USER NOT LOGGED IN)
 * ======================================================
 */
Route::prefix('portal/security')
    ->name('customer.security.')
    ->group(function () {

        // Send 2FA code after successful password check
        Route::post('/email/send-login-code', [SecurityController::class, 'sendLogin2FACode'])
            ->name('email.send-login-code');

        // Verify login-time 2FA code
        Route::post('/email/verify-login-code', [SecurityController::class, 'verifyLogin2FACode'])
            ->name('email.verify-login-code');
    });
