<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\SecurityController;

Route::middleware(['auth'])
    ->prefix('portal/security')
    ->name('customer.security.')
    ->group(function () {

        // Already exists:
        Route::post('/2fa/email/toggle', [SecurityController::class, 'toggleEmail'])
            ->name('2fa.email.toggle');

        // NEW — Send verification code (email 2FA)
        Route::post('/email/send-code', [SecurityController::class, 'sendEmail2FACode'])
            ->name('email.send-code');

        // NEW — Verify code (email 2FA)
        Route::post('/email/verify-code', [SecurityController::class, 'verifyEmail2FACode'])
            ->name('email.verify-code');

    });
