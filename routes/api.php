<?php

use App\Http\Controllers\Admin\Api\DeviceAuditApiController;
use Illuminate\Support\Facades\Route;

Route::post('/device-audit', [DeviceAuditApiController::class, 'store']);