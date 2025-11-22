<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\SecurityController;

// ...your existing routes above...

Route::middleware(['auth']) // keep same middleware you already use
    ->prefix('portal')
    ->name('customer.')
    ->group(function () {

        // other portal routes...

        // AJAX: toggle Email 2FA on/off
        Route::post('/security/2fa/email/toggle', [SecurityController::class, 'toggleEmail'])
            ->name('security.2fa.email.toggle');
    });
