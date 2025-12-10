<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScamCheckerController;

// Scam Checker - basic page + analysis handler
Route::get('/scam-checker', [ScamCheckerController::class, 'index']);
Route::post('/scam-checker', [ScamCheckerController::class, 'analyze']);