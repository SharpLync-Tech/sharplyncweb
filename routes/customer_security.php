<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\SecurityController;

/**
 * ======================================================
 *  PORTAL 2FA ROUTES (USER IS LOGGED IN)
 * ======================================================
 */
Route::middleware(['auth'])
    ->prefix('portal/security')
    ->name('customer.security.')
    ->group(function () {

        // Toggle Email 2FA (on/off)
        Route::post('/2fa/email/toggle', [SecurityController::class, 'toggleEmail'])
            ->name('2fa.email.toggle');

        // Send verification code for enabling Email 2FA
        Route::post('/email/send-code', [SecurityController::class, 'sendEmail2FACode'])
            ->name('email.send-code');

        // Verify code when enabling Email 2FA
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

        // Send 2FA code after password login
        Route::post('/email/send-login-code', [SecurityController::class, 'sendLogin2FACode'])
            ->name('email.send-login-code');

        // Verify login-time 2FA code
        Route::post('/email/verify-login-code', [SecurityController::class, 'verifyLogin2FACode'])
            ->name('email.verify-login-code');
    });
