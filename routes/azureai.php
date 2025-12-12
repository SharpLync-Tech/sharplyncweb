<?php

use Illuminate\Support\Facades\Route;
use App\ScamCheck\Controllers\ScamCheckerController;

Route::get('/scam-checker', [ScamCheckerController::class, 'index']);
Route::post('/scam-checker', [ScamCheckerController::class, 'analyze']);

Route::get('/threatcheck-info', function () {
    return view('scamcheck.gate');
});
