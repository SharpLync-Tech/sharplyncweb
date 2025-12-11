<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScamCheckerController;

// Scam Checker - basic page + analysis handler
Route::get('/scam-checker', [ScamCheckerController::class, 'index']);
Route::post('/scam-checker', [ScamCheckerController::class, 'analyze']);


Route::get('/ai/debug-model', function () {
    return [
        'endpoint' => env('AZURE_OPENAI_ENDPOINT'),
        'deployment' => env('AZURE_OPENAI_DEPLOYMENT'),
    ];
});
