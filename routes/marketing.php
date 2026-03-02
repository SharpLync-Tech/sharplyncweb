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


    // ============================
    // Admin Campaign UI
    // ============================

    Route::get('/admin/campaigns', [CampaignController::class, 'index'])
        ->name('marketing.admin.campaigns');

    Route::get('/admin/campaigns/create', [CampaignController::class, 'create'])
        ->name('marketing.admin.campaigns.create');

    Route::post('/admin/campaigns', [CampaignController::class, 'store'])
        ->name('marketing.admin.campaigns.store');

    Route::post('/admin/campaigns/{id}/send', [CampaignController::class, 'sendNowWeb'])
        ->name('marketing.admin.campaigns.send');

});