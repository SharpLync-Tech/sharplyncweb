<?php

use App\Http\Controllers\Admin\Api\DeviceAuditApiController;
use App\Http\Controllers\SharpFleet\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/device-audit', [DeviceAuditApiController::class, 'store']);


Route::post('/app/sharpfleet/stripe/webhook', [StripeWebhookController::class, 'handle']);
