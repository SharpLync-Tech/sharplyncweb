<?php

use App\Http\Controllers\Admin\Api\DeviceAuditApiController;

Route::post('/device-audit', [DeviceAuditApiController::class, 'store']);