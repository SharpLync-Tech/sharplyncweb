<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;

Route::middleware('web')->group(function () {
    
    Route::post('/submit-contact', [ContactController::class, 'submit'])
        ->name('contact.submit');
});
