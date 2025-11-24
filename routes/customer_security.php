<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\SecurityController;
use App\Http\Controllers\Customer\Auth\TwoFactorLoginController;

Route::middleware(['auth:customer'])
    ->prefix('portal/security')
    ->name('customer.security.')
    ->group(function () {

        // EMAIL 2FA TOGGLE (legacy, currently unused but kept intact)
        Route::post('/2fa/email/toggle', [SecurityController::class, 'toggleEmail'])
            ->name('2fa.email.toggle');

        // EMAIL 2FA — SEND CODE
        Route::post('/email/send-code', [SecurityController::class, 'sendEmail2FACode'])
            ->name('email.send-code');

        // EMAIL 2FA — VERIFY CODE
        Route::post('/email/verify-code', [SecurityController::class, 'verifyEmail2FACode'])
            ->name('email.verify-code');

        // EMAIL 2FA — DISABLE (NEW)
        Route::post('/email/disable', [SecurityController::class, 'disableEmail2FA'])
            ->name('email.disable');

        // AUTH APP — START SETUP
        Route::post('/auth/start', [SecurityController::class, 'startApp2FASetup'])
            ->name('auth.start');

        // AUTH APP — VERIFY SETUP
        Route::post('/auth/verify', [SecurityController::class, 'verifyApp2FASetup'])
            ->name('auth.verify');

        // AUTH APP — DISABLE
        Route::post('/auth/disable', [SecurityController::class, 'disableApp2FA'])
            ->name('auth.disable');
    });

/**
 * LOGIN-TIME 2FA
 */
Route::prefix('login/2fa')
    ->name('customer.login.2fa.')
    ->group(function () {

        Route::post('/send',   [TwoFactorLoginController::class, 'send'])
            ->name('send');

        Route::post('/verify', [TwoFactorLoginController::class, 'verify'])
            ->name('verify');
    });
