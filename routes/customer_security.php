<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\SecurityController;
use App\Http\Controllers\Customer\Auth\TwoFactorLoginController;

Route::middleware(['auth:customer'])
    ->prefix('portal/security')
    ->name('customer.security.')
    ->group(function () {

        // EMAIL 2FA
        Route::post('/email/send-code', [SecurityController::class, 'sendEmail2FACode'])
            ->name('email.send-code');

        Route::post('/email/verify-code', [SecurityController::class, 'verifyEmail2FACode'])
            ->name('email.verify-code');

        Route::post('/email/disable', [SecurityController::class, 'disableEmail2FA'])
            ->name('email.disable');

        // AUTHENTICATOR
        Route::post('/auth/start', [SecurityController::class, 'startApp2FASetup'])
            ->name('auth.start');

        Route::post('/auth/verify', [SecurityController::class, 'verifyApp2FASetup'])
            ->name('auth.verify');

        Route::post('/auth/disable', [SecurityController::class, 'disableApp2FA'])
            ->name('auth.disable');

        // SSPIN
        Route::post('/sspin/generate', [SecurityController::class, 'generateSSPIN'])
            ->name('sspin.generate');

        Route::post('/sspin/save', [SecurityController::class, 'saveSSPIN'])
            ->name('sspin.save');

        // PASSWORD RESET (correct fix)
        Route::post('/password/send-reset-link', [SecurityController::class, 'requestPasswordReset'])
            ->name('password.send-reset-link');
    });

Route::prefix('login/2fa')
    ->name('customer.login.2fa.')
    ->group(function () {
        Route::post('/send',   [TwoFactorLoginController::class, 'send'])
            ->name('send');

        Route::post('/verify', [TwoFactorLoginController::class, 'verify'])
            ->name('verify');
    });
