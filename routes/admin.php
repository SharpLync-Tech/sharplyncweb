<?php
/**
 * SharpLync Admin Routes
 * Version: 1.0
 * Description:
 *  - Routes for the SharpLync admin portal
 *  - All prefixed under /admin
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\MicrosoftController;

Route::get('/admin/login', [MicrosoftController::class, 'redirectToMicrosoft'])->name('login');
Route::get('/auth/callback', [MicrosoftController::class, 'handleCallback']);
Route::get('/admin/logout', [MicrosoftController::class, 'logout'])->name('logout');

// Grouped under /admin
Route::middleware(['web', 'admin.auth'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});