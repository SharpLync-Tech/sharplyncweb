<?php
/**
 * SharpLync Admin Routes
 * Version: 1.3
 * Last updated: 07 Nov 2025 by Max (ChatGPT)
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\Auth\MicrosoftController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\LogViewerController;

Route::get('/admin/signin', fn () => view('admin.auth.login'))->name('admin.signin');
Route::get('/admin/login', [MicrosoftController::class, 'redirectToMicrosoft'])->name('login');
Route::get('/auth/callback', [MicrosoftController::class, 'handleCallback']);
Route::get('/admin/logout', [MicrosoftController::class, 'logout'])->name('logout');

Route::middleware(['web', 'admin.auth'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Testimonials
    Route::get('/testimonials', [TestimonialController::class, 'index'])->name('admin.testimonials.index');
    Route::get('/testimonials/create', [TestimonialController::class, 'create'])->name('admin.testimonials.create');
    Route::post('/testimonials/store', [TestimonialController::class, 'store'])->name('admin.testimonials.store');
    Route::get('/testimonials/{id}/edit', [TestimonialController::class, 'edit'])->name('admin.testimonials.edit');
    Route::put('/testimonials/{id}', [TestimonialController::class, 'update'])->name('admin.testimonials.update');
    Route::delete('/testimonials/{id}', [TestimonialController::class, 'destroy'])->name('admin.testimonials.destroy');
});