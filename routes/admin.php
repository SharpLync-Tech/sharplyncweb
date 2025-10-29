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

// Grouped under /admin
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});