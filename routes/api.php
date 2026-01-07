<?php

use App\Http\Controllers\Api\DeviceAuditApiController;
use App\Http\Controllers\Api\MobileAuthController;
use Illuminate\Support\Facades\Route;

Route::post('/device-audit', [DeviceAuditApiController::class, 'store']);

Route::post('/mobile/login', [MobileAuthController::class, 'login']);