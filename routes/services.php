<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Services\ServicesController;

Route::get('/services', [ServicesController::class, 'index'])
    ->name('services');

Route::view('/it-support-stanthorpe', 'pages.it-support-stanthorpe')
    ->name('it-support.stanthorpe');

Route::view('/computer-repairs-stanthorpe', 'pages.computer-repairs-stanthorpe')
    ->name('computer-repairs.stanthorpe');

// Preserve legacy homepage links and any external bookmarks.
Route::redirect('/services/security', '/services#cybersecurity', 301);
Route::redirect('/services/cloud', '/services#cloud-m365', 301);
Route::redirect('/services/support', '/it-support-stanthorpe', 301);
