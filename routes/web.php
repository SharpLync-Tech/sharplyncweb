<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Auth\VerifyController;
use App\Http\Controllers\Admin\LogViewerController;
use App\Http\Controllers\PolicyController;

use App\Models\CMS\Service;
use App\Models\CMS\MenuItem;

Route::get('/', fn() => view('welcome'));
Route::get('/contact', fn() => view('contact'));
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



// Policy Pages
Route::get('/terms', [PolicyController::class, 'termsAndConditions'])->name('terms');
Route::get('/privacy', [PolicyController::class, 'privacyPolicy'])->name('privacy');


Route::get('/test-services', function () {
    return Service::all();    
});

Route::get('/test-menu', function () {
    return MenuItem::all();
});



require __DIR__.'/facilities.php';
require __DIR__.'/admin.php';
require __DIR__.'/customers.php';
require __DIR__.'/services.php';
