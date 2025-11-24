<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\SecurityController;
use App\Http\Controllers\Customer\Auth\TwoFactorLoginController;

/**
 * ======================================================
 *  PORTAL 2FA ROUTES (USER IS LOGGED IN)
 * ======================================================
 * These routes are used from inside the Customer Portal
 * when the user enables 2FA from settings.
 * ======================================================
 */
Route::middleware(['auth:customer'])
    ->prefix('portal/security')
    ->name('customer.security.')
    ->group(function () {

        // Enable/disable email 2FA toggle (future use)
        Route::post('/2fa/email/toggle', [SecurityController::class, 'toggleEmail'])
            ->name('2fa.email.toggle');

        // EMAIL 2FA SETUP — SEND CODE
        Route::post('/email/send-code', [SecurityController::class, 'sendEmail2FACode'])
            ->name('email.send-code');

        // EMAIL 2FA SETUP — VERIFY CODE
        Route::post('/email/verify-code', [SecurityController::class, 'verifyEmail2FACode'])
            ->name('email.verify-code');

        // AUTHENTICATOR APP 2FA — START SETUP
        Route::post('/auth/start', [SecurityController::class, 'startApp2FASetup'])
            ->name('auth.start');

        // AUTHENTICATOR APP 2FA — VERIFY & ENABLE
        Route::post('/auth/verify', [SecurityController::class, 'verifyApp2FASetup'])
            ->name('auth.verify');

        // AUTHENTICATOR APP 2FA — DISABLE
        Route::post('/auth/disable', [SecurityController::class, 'disableApp2FA'])
            ->name('auth.disable');
    });

/**
 * ======================================================
 *  LOGIN-TIME 2FA ROUTES (USER NOT LOGGED IN)
 * ======================================================
 * These routes are used when logging in:
 * - Email code verification
 * - Authenticator app (TOTP) verification
 * Both handled by TwoFactorLoginController
 * ======================================================
 */
Route::prefix('login/2fa')
    ->name('customer.login.2fa.')
    ->group(function () {

        // LOGIN 2FA — SEND (EMAIL CODE)
        Route::post('/send', [TwoFactorLoginController::class, 'send'])
            ->name('send');

        // LOGIN 2FA — VERIFY (EMAIL OR AUTH APP)
        Route::post('/verify', [TwoFactorLoginController::class, 'verify'])
            ->name('verify');

        // LOGIN 2FA — AUTH APP TOTP (modal expects this)
        Route::post('/verify-app', [TwoFactorLoginController::class, 'verify'])
            ->name('verify-app');
    });
