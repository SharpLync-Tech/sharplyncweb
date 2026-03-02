<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Marketing\SubscriptionController;
use App\Http\Controllers\Marketing\CampaignController;

Route::prefix('marketing')->group(function () {

    // Subscriber flows
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])
        ->name('marketing.subscribe');

    Route::get('/confirm/{token}', [SubscriptionController::class, 'confirm'])
        ->name('marketing.confirm');

    Route::get('/unsubscribe/{token}', [SubscriptionController::class, 'unsubscribe'])
        ->name('marketing.unsubscribe');

    // Campaign sending (v1 operational endpoints - will be locked down later)
    Route::post('/campaigns/{id}/send-now', [CampaignController::class, 'sendNow'])
        ->name('marketing.campaigns.sendNow');

    Route::post('/campaigns/process-scheduled', [CampaignController::class, 'processScheduled'])
        ->name('marketing.campaigns.processScheduled');

});