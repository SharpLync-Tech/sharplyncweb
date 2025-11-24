<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\SecurityController;
use App\Http\Controllers\Customer\Auth\TwoFactorLoginController;

Route::middleware(['auth:customer'])
    ->prefix('portal/security')
    ->name('customer.security.')
    ->group(function () {

        Route::post('/2fa/email/toggle', [SecurityController::class, 'toggleEmail'])
            ->name('2fa.email.toggle');

        Route::post('/email/send-code', [SecurityController::class, 'sendEmail2FACode'])
            ->name('email.send-code');

        Route::post('/email/verify-code', [SecurityController::class, 'verifyEmail2FACode'])
            ->name('email.verify-code');

        Route::post('/email/disable', [SecurityController::class, 'disableEmail2FA'])
            ->name('email.disable');

        Route::post('/auth/start', [SecurityController::class, 'startApp2FASetup'])
            ->name('auth.start');

        Route::post('/auth/verify', [SecurityController::class, 'verifyApp2FASetup'])
            ->name('auth.verify');

        Route::post('/auth/disable', [SecurityController::class, 'disableApp2FA'])
            ->name('auth.disable');
    });

Route::prefix('login/2fa')
    ->name('customer.login.2fa.')
    ->group(function () {

        Route::post('/send', [TwoFactorLoginController::class, 'send'])
            ->name('send');

        Route::post('/verify', [TwoFactorLoginController::class, 'verify'])
            ->name('verify');
    });
