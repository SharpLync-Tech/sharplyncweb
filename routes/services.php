<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Services\ServicesController;

Route::get('/services', [ServicesController::class, 'index'])
    ->name('services');