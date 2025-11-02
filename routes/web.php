<?php
/**
 * SharpLync Web Routes
 * Version: 1.1
 * Description:
 *  - Base public routes for SharpLync website
 *  - Added admin route inclusion (routes/admin.php)
 *  - Ensures modular structure for future admin portal
 */

use Illuminate\Support\Facades\Route;

// ==============================
// Public Routes
// ==============================

Route::get('/', function () {
    return view('welcome');
});

Route::get('/style-preview', function () {
    return view('style-preview');
});

Route::get('/mobile-preview', function () {
    return view('mobile-preview');
});

Route::get('/components', function () {
    return view('components');
});

Route::get('/home', function () {
    return view('home');
});

Route::get('/test-threatpulse', function () {
    return view('test-threatpulse');
});

Route::get('/test', function () {
    return view('test');
});

// ==============================
// Admin Routes (modular include)
// ==============================

require __DIR__ . '/admin.php';