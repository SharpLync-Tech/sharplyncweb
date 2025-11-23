<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\CRM\User;
use App\Mail\TwoFactorEmailCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('customers.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $email    = $request->input('email');
        $password = $request->input('password');

        // Look up user directly in CRM (NOT logged in yet)
        /** @var \App\Models\CRM\User|null $user */
        $user = User::where('email', $email)->first();

        Log::info('LOGIN ATTEMPT', [
            'email' => $email,
            'found' => (bool) $user,
        ]);

        if (! $user || ! Hash::check($password, $user->password)) {
            Log::warning('LOGIN FAILED', ['email' => $email]);
            return back()->with('error', 'Invalid email or password.');
        }

        if ($user->account_status === 'suspended') {
            return back()->with('error', 'Your account has been suspended. Please contact support.');
        }

        // If Email 2FA is NOT enabled → normal login
        if (! $user->two_factor_email_enabled) {
            Auth::guard('customer')->login($user);

            $user->update([
                'last_login_at' => Carbon::now(),
            ]);

            Log::info('LOGIN SUCCESS (no 2FA)', [
                'id'    => $user->id,
                'email' => $user->email,
            ]);

            return redirect()->intended('/portal');
        }

        // ============ EMAIL 2FA ENABLED: START 2FA FLOW ============

        // Store user id for 2FA step
        $request->session()->put('2fa_user_id', $user->id);

        // Mask email for display in modal
        $maskedEmail = $this->maskEmail($user->email);
        $request->session()->put('email_masked', $maskedEmail);
        $request->session()->put('show_2fa_modal', true);

        // Send login-time 2FA code (reuses CRM user_two_factor_tokens)
        $this->sendLoginCode($user);

        Log::info('LOGIN 2FA REQUIRED (email)', [
            'id'    => $user->id,
            'email' => $user->email,
        ]);

        // Just re-show the login page (modal block will render)
        return redirect()->route('customer.login')->with('status', 'We emailed you a 6-digit security code.');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'You have been logged out.');
    }

    /**
     * Helper: mask an email like "jo*****@domain.com"
     */
    private function maskEmail(?string $email): string
    {
        if (! $email || ! str_contains($email, '@')) {
            return '(no email on file)';
        }

        [$local, $domain] = explode('@', $email, 2);
        $visible = mb_substr($local, 0, 2);
        $stars   = max(1, mb_strlen($local) - 2);

        return $visible . str_repeat('*', $stars) . '@' . $domain;
    }

    /**
     * Helper: send login-time 2FA code using CRM DB
     */
    private function sendLoginCode(User $user): void
    {
        // 6-digit code
        $code = rand(100000, 999999);
        $hash = hash('sha256', $code);

        // Store token in sharplync_crm.user_two_factor_tokens
        DB::connection('crm')->table('user_two_factor_tokens')->insert([
            'user_id'    => $user->id,
            'channel'    => 'email',          // matches enum('email','sms')
            'token_hash' => $hash,
            'sent_to'    => $user->email,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
        ]);

        // Send email with code
        Mail::to($user->email)->send(new TwoFactorEmailCode($user, $code));
    }
}
