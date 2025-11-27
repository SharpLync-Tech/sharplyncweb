<?php


use App\Http\Controllers\SupportAdmin\TicketController as SupportAdminTicketController;

Route::middleware(['web', 'admin.auth'])
    ->prefix('support-admin')
    ->name('support-admin.')
    ->group(function () {
        Route::get('/tickets', [SupportAdminTicketController::class, 'index'])
            ->name('tickets.index');

        Route::get('/tickets/{ticket}', [SupportAdminTicketController::class, 'show'])
            ->name('tickets.show');

        Route::patch('/tickets/{ticket}/status', [SupportAdminTicketController::class, 'updateStatus'])
            ->name('tickets.update-status');

        Route::patch('/tickets/{ticket}/priority', [SupportAdminTicketController::class, 'updatePriority'])
            ->name('tickets.update-priority');

        Route::patch('/tickets/{ticket}/quick-resolve', [SupportAdminTicketController::class, 'quickResolve'])
            ->name('tickets.quick-resolve');

        Route::post('/tickets/{ticket}/reply', [SupportAdminTicketController::class, 'reply'])
            ->name('tickets.reply');

        Route::post('/tickets/{ticket}/internal-notes', [SupportAdminTicketController::class, 'storeInternalNote'])
            ->name('tickets.internal-notes.store');
    });
