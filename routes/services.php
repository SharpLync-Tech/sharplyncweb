<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Services\ServicesMockController;

Route::get('/services-mock', [ServicesMockController::class, 'index'])
    ->name('services.mock');

Route::get('/services-mock-clean', [ServicesMockController::class, 'index'])
    ->name('services-mock-clean');
