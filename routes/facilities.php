<?php
/**
 * SharpLync Facilities Routes
 * Version: 1.0
 * Description:
 *  - Public routes for the SharpLync Facilities microsite
 *  - Uses the sharplync_facilities database connection
 *  - Organized under /facilities prefix
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Facilities\FacilitiesHomeController;
use App\Http\Controllers\Facilities\FacilitiesAboutController;
use App\Http\Controllers\Facilities\FacilitiesServicesController;
use App\Http\Controllers\Facilities\FacilitiesContactController;

// ==============================
// Facilities Microsite Routes
// ==============================
Route::prefix('facilities')->name('facilities.')->group(function () {

    // Home Page
    Route::get('/', [FacilitiesHomeController::class, 'index'])->name('home');

    // About Page
    Route::get('/about', [FacilitiesAboutController::class, 'index'])->name('about');

    // Services Overview
    Route::get('/services', [FacilitiesServicesController::class, 'index'])->name('services');

    // Contact / Request Concept
    Route::get('/contact', [FacilitiesContactController::class, 'index'])->name('contact');
    Route::post('/contact', [FacilitiesContactController::class, 'submit'])->name('contact.submit');

});