<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    // ... constructor/other methods if any

    /**
     * Handle login attempt (POST /login)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Explicitly use 'customer' guard
        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate(); // Fresh session
            $this->logLoginSuccess($request); // Optional: Your logging

            return redirect()->intended(route('customers.dashboard')); // Post-login redirect
        }

        // Failed: Back with error
        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Log out (POST /logout)
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout(); // Clear customer session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('customer.login')); // Or home
    }

    // Optional: Log success (integrate your RegistrationLog if needed)
    private function logLoginSuccess(Request $request): void
    {
        // e.g., RegistrationLog::create([... 'status' => 'login_success']);
    }
}