<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\CRM\User;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('customers.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        Log::info('LOGIN ATTEMPT', [
            'email' => $request->email,
            'guard' => Auth::getDefaultDriver(),
            'provider' => config('auth.guards.customer.provider'),
        ]);

        // ✅ Use customer guard explicitly
        if (Auth::guard('customer')->attempt($credentials, $request->filled('remember'))) {
            $user = Auth::guard('customer')->user();

            if ($user->account_status === 'suspended') {
                Auth::guard('customer')->logout();
                return back()->with('error', 'Your account has been suspended. Please contact support.');
            }

            $user->update([
                'last_login_at' => Carbon::now(),
            ]);

            Log::info('LOGIN SUCCESS', ['id' => $user->id, 'email' => $user->email]);

            return redirect()->intended('/portal');
        }

        Log::warning('LOGIN FAILED', ['email' => $request->email]);

        return back()->with('error', 'Invalid email or password.');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'You have been logged out.');
    }
}