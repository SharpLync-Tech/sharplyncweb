<?php
use App\Http\Controllers\Marketing\CampaignController;

Route::prefix('marketing')->group(function () {

    Route::post('/campaigns/{id}/send-now', [CampaignController::class, 'sendNow']);

    Route::post('/campaigns/process-scheduled', [CampaignController::class, 'processScheduled']);

});