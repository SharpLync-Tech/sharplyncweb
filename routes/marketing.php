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

    Route::get('/preferences/{token}', [SubscriptionController::class, 'preferences'])
        ->name('marketing.preferences');

    Route::post('/preferences/{token}', [SubscriptionController::class, 'updatePreferences'])
        ->name('marketing.preferences.update');
});

// ============================
// Admin Campaign UI (SSO + Marketing Access)
// ============================
Route::middleware(['admin.auth', 'marketing.access'])
    ->prefix('marketing/admin')
    ->group(function () {
        Route::get('/campaigns', [CampaignController::class, 'index'])
            ->name('marketing.admin.campaigns');

        Route::get('/logs', [CampaignController::class, 'logs'])
            ->name('marketing.admin.logs');

        Route::post('/uploads', [CampaignController::class, 'upload'])
            ->name('marketing.admin.uploads');

        Route::get('/campaigns/create', [CampaignController::class, 'create'])
            ->name('marketing.admin.campaigns.create');

        Route::get('/campaigns/{id}/edit', [CampaignController::class, 'edit'])
            ->name('marketing.admin.campaigns.edit');

        Route::post('/campaigns', [CampaignController::class, 'store'])
            ->name('marketing.admin.campaigns.store');

        Route::post('/campaigns/{id}', [CampaignController::class, 'update'])
            ->name('marketing.admin.campaigns.update');

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

        Route::post('/campaigns/{id}/resend', [CampaignController::class, 'resend'])
            ->name('marketing.admin.campaigns.resend');

        Route::post('/campaigns/{id}/delete', [CampaignController::class, 'destroy'])
            ->name('marketing.admin.campaigns.delete');
    });
