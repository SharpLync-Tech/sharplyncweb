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
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('customers.login');
    }

    /**
     * Handle login (step 1 — password check)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $email    = $request->input('email');
        $password = $request->input('password');

        /** @var User|null $user */
        $user = User::where('email', $email)->first();

        Log::info('LOGIN ATTEMPT', [
            'email' => $email,
            'found' => (bool) $user,
        ]);

        // Wrong credentials
        if (! $user || ! Hash::check($password, $user->password)) {
            Log::warning('LOGIN FAILED', ['email' => $email]);
            return back()->with('error', 'Invalid email or password.');
        }

        // Suspended
        if ($user->account_status === 'suspended') {
            return back()->with('error', 'Your account has been suspended. Please contact support.');
        }

        // -------------------------------------------------------------------
        // Determine 2FA mode
        // -------------------------------------------------------------------
        $usesApp2FA   = (bool) $user->two_factor_app_enabled;
        $usesEmail2FA = (bool) $user->two_factor_email_enabled;

        // ===================================================================
        // CASE 1: NO 2FA ENABLED → Normal login
        // ===================================================================
        if (! $usesApp2FA && ! $usesEmail2FA) {

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

        // ===================================================================
        // CASE 2: AUTHENTICATOR APP 2FA ENABLED → TOTP FLOW
        // ===================================================================
        if ($usesApp2FA) {

            // Store ID + method in session for the 2FA step
            session([
                '2fa_user_id' => $user->id,
                '2fa_method'  => 'app',
            ]);

            Log::info('LOGIN 2FA REQUIRED (app)', [
                'id'    => $user->id,
                'email' => $user->email,
            ]);

            return redirect()
                ->route('customer.login')
                ->with('show_app_2fa_modal', true)
                ->with('status', 'Open your Authenticator app and enter your 6-digit code.');
        }

        // ===================================================================
        // CASE 3: EMAIL 2FA ENABLED → Existing email 2FA flow
        // ===================================================================

        // Store ID + method in session for the 2FA step
        session([
            '2fa_user_id' => $user->id,
            '2fa_method'  => 'email',
        ]);

        // Mask email for the modal
        $maskedEmail = $this->maskEmail($user->email);

        // Send the login-time 2FA code
        $this->sendLoginCode($user);

        Log::info('LOGIN 2FA REQUIRED (email)', [
            'id'    => $user->id,
            'email' => $user->email,
        ]);

        // Flash modal vars
        return redirect()
            ->route('customer.login')
            ->with('show_2fa_modal', true)
            ->with('email_masked', $maskedEmail)
            ->with('status', 'We emailed you a 6-digit security code.');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'You have been logged out.');
    }

    /**
     * Mask email (ja********@gmail.com)
     */
    private function maskEmail(?string $email): string
    {
        if (! $email || ! str_contains($email, '@')) {
            return '(no email on file)';
        }

        [$local, $domain] = explode('@', $email, 2);

        if (strlen($local) <= 2) {
            $visible = substr($local, 0, 1);
            $stars   = max(1, strlen($local) - 1);
        } else {
            $visible = substr($local, 0, 2);
            $stars   = max(1, strlen($local) - 2);
        }

        return $visible . str_repeat('*', $stars) . '@' . $domain;
    }

    /**
     * Sends login-time 2FA code to the user (EMAIL)
     */
    private function sendLoginCode(User $user): void
    {
        $code = rand(100000, 999999);
        $hash = hash('sha256', $code);

        // Store token in CRM DB
        DB::connection('crm')->table('user_two_factor_tokens')->insert([
            'user_id'    => $user->id,
            'channel'    => 'email',
            'token_hash' => $hash,
            'sent_to'    => $user->email,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
        ]);

        // Email the code
        Mail::to($user->email)->send(new TwoFactorEmailCode($user, $code));

        Log::info('LOGIN 2FA CODE SENT', [
            'user_id' => $user->id,
            'email'   => $user->email,
        ]);
    }
}
