<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    /**
     * Handle login attempt (POST /login)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Try to authenticate user using the customer guard
        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {

            $user = Auth::guard('customer')->user();

            // If 2FA is enabled → DO NOT log in fully
            if ($user->two_factor_enabled) {

                // Store ID temporarily
                session([
                    '2fa:user:id' => $user->id,
                    '2fa:method'  => $user->two_factor_method,
                ]);

                // Logout the partial session
                Auth::guard('customer')->logout();

                // Redirect back with modal flag
                return redirect()
                    ->back()
                    ->with('show_2fa_modal', true)
                    ->with('email_masked', $this->maskEmail($user->email));
            }

            // No 2FA → proceed normally
            $request->session()->regenerate();

            return redirect()->intended(route('customer.portal'));
        }

        // Failed login
        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Mask email (j****e@example.com)
     */
    private function maskEmail(string $email)
    {
        [$name, $domain] = explode('@', $email);

        if (strlen($name) <= 2) {
            $name = substr($name, 0, 1) . str_repeat('*', strlen($name) - 1);
        } else {
            $name = substr($name, 0, 1)
                . str_repeat('*', strlen($name) - 2)
                . substr($name, -1);
        }

        return $name . '@' . $domain;
    }

    /**
     * Log out (POST /logout)
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/');
    }
}
