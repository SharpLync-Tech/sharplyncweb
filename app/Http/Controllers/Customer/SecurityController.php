<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\CRM\User;
use App\Mail\TwoFactorEmailCode;
use Carbon\Carbon;
use PragmaRX\Google2FA\Google2FA;

class SecurityController extends Controller
{
    /* ============================================================
     |  PORTAL 2FA — SEND CODE (Enable Email 2FA)
     * ============================================================ */
    public function sendEmail2FACode(Request $request)
    {
        /** @var User $user */
        $user = auth('customer')->user();

        if (!$user) {
            Log::error("PORTAL 2FA SEND ERROR: No authenticated customer.");
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

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

        Log::info("PORTAL 2FA CODE SENT", ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent.'
        ]);
    }

    /* ============================================================
     |  PORTAL 2FA — VERIFY CODE (Enable Email 2FA)
     * ============================================================ */
    public function verifyEmail2FACode(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric'
        ]);

        /** @var User $user */
        $user = auth('customer')->user();

        if (!$user) {
            Log::error("PORTAL VERIFY ERROR: Not authenticated.");
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $hash = hash('sha256', $request->code);

        $record = DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->where('token_hash', $hash)
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();

        if (!$record) {
            Log::warning("PORTAL 2FA INVALID CODE", ['user_id' => $user->id]);
            return response()->json(['success' => false, 'message' => 'Invalid or expired code'], 422);
        }

        $user->two_factor_email_enabled = 1;
        $user->two_factor_confirmed_at  = now();
        $user->save();

        DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->delete();

        Log::info("PORTAL 2FA ENABLED (EMAIL)", ['user_id' => $user->id]);

        return response()->json(['success' => true, 'message' => 'Email authentication enabled.']);
    }

    /* ============================================================
     |  PORTAL 2FA — AUTHENTICATOR APP (GOOGLE AUTHENTICATOR)
     * ============================================================ */

    /**
     * Start Authenticator App setup:
     * - Generate a new TOTP secret
     * - Save to user
     * - Return otpauth:// URL + secret so the frontend can show QR + manual code
     */
    public function startApp2FASetup(Request $request)
    {
        /** @var User $user */
        $user = auth('customer')->user();

        if (!$user) {
            Log::error("PORTAL APP 2FA START: Not authenticated.");
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $google2fa = new Google2FA();

        // Generate a fresh secret every time setup is started
        $secret = $google2fa->generateSecretKey();

        $user->two_factor_secret      = $secret;
        $user->two_factor_app_enabled = 0;  // Not enabled until verified
        $user->save();

        // Build standard otpauth URL (works with Google Authenticator, MS Authenticator, etc.)
        $issuer   = 'SharpLync';
        $account  = $user->email ?: ('customer-' . $user->id . '@sharplync.local');
        $otpauth  = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            rawurlencode($issuer),
            rawurlencode($account),
            $secret,
            rawurlencode($issuer)
        );

        Log::info("PORTAL APP 2FA STARTED", ['user_id' => $user->id]);

        return response()->json([
            'success'     => true,
            'secret'      => $secret,
            'otpauth_url' => $otpauth,
            'message'     => 'Authenticator setup started.'
        ]);
    }

    /**
     * Verify the 6-digit code from the Authenticator app to enable TOTP 2FA.
     */
    public function verifyApp2FASetup(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        /** @var User $user */
        $user = auth('customer')->user();

        if (!$user) {
            Log::error("PORTAL APP 2FA VERIFY: Not authenticated.");
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        if (empty($user->two_factor_secret)) {
            Log::warning("PORTAL APP 2FA VERIFY: No secret on user.", ['user_id' => $user->id]);
            return response()->json([
                'success' => false,
                'message' => 'Authenticator setup has not been started.'
            ], 422);
        }

        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (!$valid) {
            Log::warning("PORTAL APP 2FA INVALID CODE", ['user_id' => $user->id]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired code.'
            ], 422);
        }

        // Enable app-based 2FA and turn off email 2FA
        $user->two_factor_app_enabled    = 1;
        $user->two_factor_email_enabled  = 0;
        $user->two_factor_confirmed_at   = now();
        $user->two_factor_default_method = 'app'; // optional, nothing uses this yet
        $user->save();

        Log::info("PORTAL APP 2FA ENABLED", ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'Authenticator app has been enabled.'
        ]);
    }

    /**
     * Disable Authenticator App 2FA.
     */
    public function disableApp2FA(Request $request)
    {
        /** @var User $user */
        $user = auth('customer')->user();

        if (!$user) {
            Log::error("PORTAL APP 2FA DISABLE: Not authenticated.");
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $user->two_factor_app_enabled      = 0;
        // We can optionally clear the secret; leaving it null forces a new setup next time
        $user->two_factor_secret           = null;
        $user->two_factor_trusted_devices  = null; // clear any trusted devices tied to app 2FA
        $user->save();

        Log::info("PORTAL APP 2FA DISABLED", ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'Authenticator app has been disabled.'
        ]);
    }

    /* ============================================================
     |  LOGIN-TIME 2FA — SEND LOGIN CODE
     * ============================================================ */
    public function sendLogin2FACode(Request $request)
    {
        $userId = session('2fa_user_id');

        if (!$userId) {
            Log::warning("LOGIN 2FA SEND: No 2fa_user_id found.");
            return response()->json(['success' => false, 'message' => 'Session expired.'], 419);
        }

        /** @var User $user */
        $user = User::find($userId);

        if (!$user) {
            Log::error("LOGIN 2FA SEND: User not found", ['id' => $userId]);
            return response()->json(['success' => false, 'message' => 'Invalid session user'], 419);
        }

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

        Log::info("LOGIN 2FA CODE SENT", ['user_id' => $user->id]);

        return response()->json(['success' => true, 'message' => 'Code re-sent']);
    }

    /* ============================================================
     |  LOGIN-TIME 2FA — VERIFY LOGIN CODE
     * ============================================================ */
    public function verifyLogin2FACode(Request $request)
    {
        $request->validate(['code' => 'required|numeric']);

        $userId = session('2fa_user_id');

        if (!$userId) {
            Log::warning("LOGIN VERIFY: No 2fa_user_id in session");
            return response()->json(['success' => false, 'message' => '2FA session expired. Please log in again.'], 419);
        }

        /** @var User $user */
        $user = User::find($userId);

        if (!$user) {
            Log::error("LOGIN VERIFY: User not found", ['id' => $userId]);
            return response()->json(['success' => false, 'message' => 'Invalid user'], 419);
        }

        $hash = hash('sha256', $request->code);

        $record = DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->where('token_hash', $hash)
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();

        if (!$record) {
            Log::warning("LOGIN VERIFY INVALID CODE", ['user_id' => $user->id]);
            return response()->json(['success' => false, 'message' => 'Invalid or expired code.'], 422);
        }

        // Clean tokens
        DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->delete();

        // Finalise login
        auth('customer')->login($user);

        $user->last_login_at = now();
        $user->save();

        Log::info("LOGIN 2FA SUCCESS", ['user_id' => $user->id]);

        return response()->json([
            'success'  => true,
            'redirect' => route('customer.portal')
        ]);
    }
}
