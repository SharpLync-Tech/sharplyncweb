<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Handle login attempt (POST /login)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Explicitly use the 'customer' guard
        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate(); // Prevent session fixation
            $this->logLoginSuccess($request); // Optional logging

            // ✅ Redirect to the new /portal route
            return redirect()->intended(route('customer.portal'));
        }

        // ❌ Login failed
        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Log out (POST /logout)
     */
    public function logout(Request $request)
    {
        // ✅ Explicitly log out of the 'customer' guard
        Auth::guard('customer')->logout();

        // Invalidate and regenerate session token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ✅ Redirect back to the customer login page
        return redirect()->to('/');
    }

    /**
     * Optional: Custom login success logger
     */
    private function logLoginSuccess(Request $request): void
    {
        // Example placeholder for future logging feature
        // RegistrationLog::create([... 'status' => 'login_success']);
    }
}