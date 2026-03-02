<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Marketing\SubscriptionController;
use App\Http\Controllers\Marketing\CampaignController;

/*
|--------------------------------------------------------------------------
| Web Routes (Subscriber Flow)
|--------------------------------------------------------------------------
*/

Route::prefix('marketing')->group(function () {

    Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])
        ->name('marketing.subscribe');

    Route::get('/confirm/{token}', [SubscriptionController::class, 'confirm'])
        ->name('marketing.confirm');

    Route::get('/unsubscribe/{token}', [SubscriptionController::class, 'unsubscribe'])
        ->name('marketing.unsubscribe');

});

/*
|--------------------------------------------------------------------------
| API Routes (Campaign Sending - No CSRF)
|--------------------------------------------------------------------------
*/

Route::prefix('marketing')
    ->middleware('api')
    ->group(function () {

        Route::post('/campaigns/{id}/send-now', [CampaignController::class, 'sendNow'])
            ->name('marketing.campaigns.sendNow');

        Route::post('/campaigns/process-scheduled', [CampaignController::class, 'processScheduled'])
            ->name('marketing.campaigns.processScheduled');

    });