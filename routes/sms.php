<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestSmsController;

// ------------------------------------------------------
// SMS TEST ROUTES (sandbox only)
// ------------------------------------------------------
Route::get('/test-sms', [TestSmsController::class, 'form']);
Route::post('/test-sms/send', [TestSmsController::class, 'send']);
