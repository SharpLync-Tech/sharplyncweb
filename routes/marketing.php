<?php
dd('marketing routes loaded');
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Marketing\SubscriptionController;

Route::prefix('marketing')->group(function () {

    Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])
        ->name('marketing.subscribe');

    Route::get('/confirm/{token}', [SubscriptionController::class, 'confirm'])
        ->name('marketing.confirm');

    Route::get('/unsubscribe/{token}', [SubscriptionController::class, 'unsubscribe'])
        ->name('marketing.unsubscribe');

});