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

Route::middleware(['web', 'auth:customer'])
    ->prefix('customer/support')
    ->name('customer.support.')
    ->group(function () {

        /* -------------------------
         * Ticket Listing + Create
         * ------------------------- */
        Route::get('/', [SupportController::class, 'index'])
            ->name('index');

        Route::get('/tickets/create', [TicketController::class, 'create'])
            ->name('tickets.create');

        Route::post('/tickets', [TicketController::class, 'store'])
            ->name('tickets.store');


        /* -------------------------
         * Ticket Details
         * ------------------------- */
        Route::get('/tickets/{ticket}', [TicketController::class, 'show'])
            ->name('tickets.show');


        /* -------------------------
         * Ticket Reply
         * ------------------------- */
        Route::post('/tickets/{ticket}/reply', [TicketReplyController::class, 'store'])
            ->name('tickets.reply.store');


        /* -------------------------
         * NEW: Ticket Status Update
         * ------------------------- */
        Route::post('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])
            ->name('tickets.status.update');


        /* -------------------------
         * NEW: Ticket Priority Update
         * ------------------------- */
        Route::post('/tickets/{ticket}/priority', [TicketController::class, 'updatePriority'])
            ->name('tickets.priority.update');

        /* -------------------------
        * Download attachment
        * ------------------------- */
        Route::get('/attachment/{reply}', [TicketReplyController::class, 'download'])
            ->name('attachment.download'); 
            
        Route::get('/support/attachment/{reply}', [TicketReplyController::class, 'download'])
        ->name('support.attachment.download');

            
    });
