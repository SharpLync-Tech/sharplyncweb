<?php

use App\Http\Controllers\Admin\Api\DeviceAuditApiController;
use App\Http\Controllers\SharpFleet\StripeWebhookController;

Route::post('/device-audit', [DeviceAuditApiController::class, 'store']);

Route::post('/sharpfleet/stripe/webhook', [StripeWebhookController::class, 'handle']);
