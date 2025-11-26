<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerSupport\SupportController;
use App\Http\Controllers\CustomerSupport\TicketController;
use App\Http\Controllers\CustomerSupport\TicketReplyController;

/*
|--------------------------------------------------------------------------
| Customer Support Routes (SharpLync Support Module V1)
|--------------------------------------------------------------------------
|
| These routes are intentionally isolated in their own file.
| In routes/web.php add:
|
|   require __DIR__ . '/support.php';
|
| Adjust the middleware/guard if your customer auth guard name differs.
*/

Route::middleware(['web', 'auth.customer'])
    ->prefix('customer/support')
    ->name('customer.support.')
    ->group(function () {
        Route::get('/', [SupportController::class, 'index'])->name('index');

        Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');

        Route::get('/tickets/{ticket}', [TicketController::class, 'show'])
            ->name('tickets.show');

        Route::post('/tickets/{ticket}/reply', [TicketReplyController::class, 'store'])
            ->name('tickets.reply.store');
    });
