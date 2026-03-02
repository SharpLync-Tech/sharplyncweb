<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Marketing\SubscriptionController;
use App\Http\Controllers\Marketing\CampaignController;

Route::prefix('marketing')->group(function () {

    // ============================
    // Public Subscription Routes
    // ============================

    Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])
        ->name('marketing.subscribe');

    Route::get('/confirm/{token}', [SubscriptionController::class, 'confirm'])
        ->name('marketing.confirm');

    Route::get('/unsubscribe/{token}', [SubscriptionController::class, 'unsubscribe'])
        ->name('marketing.unsubscribe');
});

// ============================
// Admin Campaign UI (SSO + Marketing Access)
// ============================
Route::middleware(['admin.auth', 'marketing.access'])
    ->prefix('marketing/admin')
    ->group(function () {
        Route::get('/campaigns', [CampaignController::class, 'index'])
            ->name('marketing.admin.campaigns');

        Route::get('/campaigns/create', [CampaignController::class, 'create'])
            ->name('marketing.admin.campaigns.create');

        Route::post('/campaigns', [CampaignController::class, 'store'])
            ->name('marketing.admin.campaigns.store');

        Route::post('/campaigns/{id}/submit', [CampaignController::class, 'submitForReview'])
            ->name('marketing.admin.campaigns.submit');

        Route::post('/campaigns/{id}/approve', [CampaignController::class, 'approve'])
            ->name('marketing.admin.campaigns.approve');

        Route::post('/campaigns/{id}/schedule', [CampaignController::class, 'schedule'])
            ->name('marketing.admin.campaigns.schedule');

        Route::get('/campaigns/{id}/preview', [CampaignController::class, 'preview'])
            ->name('marketing.admin.campaigns.preview');

        Route::post('/campaigns/{id}/send', [CampaignController::class, 'sendNowWeb'])
            ->name('marketing.admin.campaigns.send');
    });
