<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\CRM\User;
use App\Mail\TwoFactorEmailCode;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorLoginController extends Controller
{
    /**
     * Send login-time 2FA code (EMAIL)
     */
    public function send(Request $request)
    {
        $userId = session('2fa_user_id');
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => '2FA session expired. Please log in again.'
            ], 422);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 422);
        }

        // generate 6-digit email code
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

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent.'
        ]);
    }

    /**
     * Verify login-time 2FA
     * Handles:
     * - Authenticator App (TOTP)
     * - Email fallback
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6'
        ]);

        $userId = session('2fa_user_id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => '2FA session expired. Please log in again.'
            ], 422);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 422);
        }

        $method = session('2fa_method', 'email');

        // ===============================================================
        // CASE 1 — AUTHENTICATOR APP (TOTP)
        // ===============================================================
        if ($method === 'app') {

            if (! $user->two_factor_app_enabled || empty($user->two_factor_secret)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authenticator is not enabled for this account.'
                ], 422);
            }

            $google2fa = new Google2FA();

            // 🎯 FIX: allow ±1 time window drift = reliable login
            $valid = $google2fa->setWindow(2)->verifyKey(
                $user->two_factor_secret,
                $request->code
            );

            if (! $valid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired code.'
                ], 422);
            }

            // success → complete login
            Auth::guard('customer')->login($user);

            // clean up session flags
            session()->forget('2fa_user_id');
            session()->forget('2fa_method');
            session()->forget('show_app_2fa_modal');

            // update last login
            $user->last_login_at = now();
            $user->save();

            return response()->json([
                'success'  => true,
                'redirect' => url('/portal')
            ]);
        }

        // ===============================================================
        // CASE 2 — EMAIL 2FA
        // ===============================================================

        $hash = hash('sha256', $request->code);

        $record = DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->where('token_hash', $hash)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired code.'
            ], 422);
        }

        // success → wipe tokens
        DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->delete();

        // Log in
        Auth::guard('customer')->login($user);

        session()->forget('2fa_user_id');
        session()->forget('email_masked');
        session()->forget('show_2fa_modal');
        session()->forget('2fa_method');

        return response()->json([
            'success' => true,
            'redirect' => url('/portal')
        ]);
    }
}
