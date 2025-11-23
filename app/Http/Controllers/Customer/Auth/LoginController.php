<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\CRM\User;
use App\Mail\TwoFactorEmailCode;
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
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $email    = $request->input('email');
        $password = $request->input('password');

        /** @var \App\Models\CRM\User|null $user */
        $user = User::where('email', $email)->first();

        Log::info('LOGIN ATTEMPT', [
            'email' => $email,
            'found' => (bool) $user,
        ]);

        // Bad credentials
        if (! $user || ! Hash::check($password, $user->password)) {
            Log::warning('LOGIN FAILED', ['email' => $email]);
            return back()->with('error', 'Invalid email or password.');
        }

        // Suspended
        if ($user->account_status === 'suspended') {
            return back()->with('error', 'Your account has been suspended. Please contact support.');
        }

        // ================================================================
        // CASE 1: 2FA NOT ENABLED → Normal login, no modal
        // ================================================================
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

        // ================================================================
        // CASE 2: 2FA ENABLED → Trigger modal + send verification code
        // ================================================================

        // Mask email
        $maskedEmail = $this->maskEmail($user->email);

        // Send login code
        $this->sendLoginCode($user);

        Log::info('LOGIN 2FA REQUIRED (email)', [
            'id'    => $user->id,
            'email' => $user->email,
        ]);

        // Return to login with FLASHED modal variables
        return redirect()
            ->route('customer.login')
            ->with('show_2fa_modal', true)
            ->with('email_masked', $maskedEmail)
            ->with('2fa_user_id', $user->id)
            ->with('status', 'We emailed you a 6-digit security code.');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'You have been logged out.');
    }

    // ---------------------------------------------------------
    // EMAIL MASK
    // ---------------------------------------------------------
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

    // ---------------------------------------------------------
    // SEND LOGIN-TIME 2FA CODE (CRM DB)
    // ---------------------------------------------------------
    private function sendLoginCode(User $user): void
    {
        $code = rand(100000, 999999);
        $hash = hash('sha256', $code);

        DB::connection('crm')->table('user_two_factor_tokens')->insert([
            'user_id'    => $user->id,
            'channel'    => 'email',
            'token_hash' => $hash,
            'sent_to'    => $user->email,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
        ]);

        Mail::to($user->email)->send(new TwoFactorEmailCode($user, $code));
    }
}
