<?php
/**
 * SharpLync Customer Routes
 * Version: 1.0
 * Description:
 *  Handles public customer onboarding and profile setup.
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\Auth\LoginController;

// ==============================
// Customer Registration & Onboarding
// ==============================

// Email-based registration and verification
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');


// Password setup after email verification
Route::get('/set-password/{id}', [RegisterController::class, 'showPasswordForm'])->name('password.create');
Route::post('/set-password/{id}', [RegisterController::class, 'savePassword'])->name('password.store');

// Customer onboarding (CRM-linked profile setup)
Route::get('/customer/setup-profile', [ProfileController::class, 'create'])->name('profile.create');
Route::post('/customer/setup-profile', [ProfileController::class, 'store'])->name('profile.store');

// Profile edit (for existing customers)
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');


// Onboarding complete screen
Route::get('/customer/onboard-complete', function () {
    return view('customers.onboard-complete');
})->name('onboard.complete');

// Temporary testing route (if needed)
Route::get('/onboard', [CustomerController::class, 'create'])->name('customers.create');
Route::post('/onboard', [CustomerController::class, 'store'])->name('customers.store');

// Customer Login route
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('customer.login');
Route::post('/login', [LoginController::class, 'login'])->name('customer.login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('customer.logout');

// Customer Dashboard route
Route::get('/dashboard', function () {
    return view('customers.dashboard');
})->name('customers.dashboard')->middleware('auth:customer');