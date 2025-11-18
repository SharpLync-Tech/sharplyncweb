<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Auth\VerifyController;
use App\Http\Controllers\Admin\LogViewerController;

Route::get('/', fn() => view('welcome'));
Route::get('/style-preview', fn() => view('style-preview'));
Route::get('/mobile-preview', fn() => view('mobile-preview'));
Route::get('/components', fn() => view('components'));
Route::get('/home', fn() => view('home'));
Route::get('/test-threatpulse', fn() => view('test-threatpulse'));

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/testimonials', [PageController::class, 'testimonials'])->name('testimonials');

// ✅ Always bind verify to VerifyController
Route::get('/verify/{token}', [VerifyController::class, 'verify'])->name('verify.email');

// Log Test - Remove in Prod

Route::get('/admin/registration-log', [LogViewerController::class, 'index'])->name('admin.registration.log');
Route::post('/admin/registration-log/clear', [LogViewerController::class, 'clear'])->name('admin.registration.log.clear');



require __DIR__.'/facilities.php';
require __DIR__.'/admin.php';
require __DIR__.'/customers.php';
require __DIR__.'/services.php';
