<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use App\Models\CRM\User;
use App\Mail\TwoFactorEmailCode;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class SecurityController extends Controller
{
    /* ============================================================
     | EMAIL 2FA — SEND CODE
     * ============================================================ */
    public function sendEmail2FACode(Request $request)
    {
        $user = auth('customer')->user();
        if (!$user) {
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

        return response()->json(['success' => true, 'message' => 'Verification code sent.']);
    }

    /* ============================================================
     | EMAIL 2FA — VERIFY CODE
     * ============================================================ */
    public function verifyEmail2FACode(Request $request)
    {
        $request->validate(['code' => 'required|numeric']);

        $user = auth('customer')->user();
        if (!$user) {
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
            return response()->json(['success' => false, 'message' => 'Invalid or expired code'], 422);
        }

        $user->two_factor_email_enabled = 1;
        $user->two_factor_confirmed_at  = now();
        $user->save();

        DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->delete();

        return response()->json(['success' => true, 'message' => 'Email authentication enabled.']);
    }

    /* ============================================================
     | EMAIL 2FA — DISABLE (NEW)
     * ============================================================ */
    public function disableEmail2FA(Request $request)
    {
        $user = auth('customer')->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $user->two_factor_email_enabled = 0;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Email authentication disabled.']);
    }

    /* ============================================================
     | AUTH APP — START SETUP
     * ============================================================ */
    public function startApp2FASetup(Request $request)
    {
        $user = auth('customer')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $google2fa = new Google2FA();
        $secret    = $google2fa->generateSecretKey();

        $user->two_factor_secret      = $secret;
        $user->two_factor_app_enabled = 0;
        $user->save();

        $issuer  = 'SharpLync';
        $account = $user->email ?: ('customer-' . $user->id);

        $otpauth = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            rawurlencode($issuer),
            rawurlencode($account),
            $secret,
            rawurlencode($issuer)
        );

        try {
            $renderer = new ImageRenderer(new RendererStyle(200), new SvgImageBackEnd());
            $writer  = new Writer($renderer);
            $svgData = $writer->writeString($otpauth);
            $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($svgData);
        } catch (\Throwable $e) {
            return response()->json([
                'success'     => true,
                'secret'      => $secret,
                'otpauth_url' => $otpauth,
                'qr_image'    => null
            ]);
        }

        return response()->json([
            'success'     => true,
            'secret'      => $secret,
            'otpauth_url' => $otpauth,
            'qr_image'    => $qrBase64
        ]);
    }

    /* ============================================================
     | AUTH APP — VERIFY SETUP
     * ============================================================ */
    public function verifyApp2FASetup(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $user = auth('customer')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $google2fa = new Google2FA();

        if (!$google2fa->verifyKey($user->two_factor_secret, $request->code)) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired code.'], 422);
        }

        $user->two_factor_app_enabled    = 1;
        $user->two_factor_email_enabled  = 0;
        $user->two_factor_default_method = 'app';
        $user->two_factor_confirmed_at   = now();
        $user->save();

        return response()->json(['success' => true, 'message' => 'Authenticator app enabled.']);
    }

    /* ============================================================
     | AUTH APP — DISABLE
     * ============================================================ */
    public function disableApp2FA(Request $request)
    {
        $user = auth('customer')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $user->two_factor_app_enabled     = 0;
        $user->two_factor_secret          = null;
        $user->two_factor_trusted_devices = null;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Authenticator app disabled.']);
    }

    /* LOGIN-TIME EMAIL 2FA (unchanged) */
    public function sendLogin2FACode(Request $request)
    {
        $userId = session('2fa_user_id');
        if (!$userId) return response()->json(['success' => false], 419);

        $user = User::find($userId);
        if (!$user) return response()->json(['success' => false], 419);

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

        return response()->json(['success' => true]);
    }

    public function verifyLogin2FACode(Request $request)
    {
        $request->validate(['code' => 'required|numeric']);

        $userId = session('2fa_user_id');
        if (!$userId) return response()->json(['success' => false], 419);

        $user = User::find($userId);
        if (!$user) return response()->json(['success' => false], 419);

        $hash = hash('sha256', $request->code);

        $record = DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->where('token_hash', $hash)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired code.'], 422);
        }

        DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->delete();

        auth('customer')->login($user);

        $user->last_login_at = now();
        $user->save();

        return response()->json([
            'success'  => true,
            'redirect' => route('customer.portal')
        ]);
    }

    /* ============================================================
     | SSPIN — GENERATE
     * ============================================================ */
    public function generateSSPIN(Request $request)
    {
        $user = auth('customer')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $newPin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::connection('crm')
            ->table('users')
            ->where('id', $user->id)
            ->update(['sspin' => $newPin]);

        $user->refresh();

        return response()->json([
            'success' => true,
            'sspin'   => $newPin
        ]);
    }

    /* ============================================================
     | SSPIN — SAVE TO DATABASE
     * ============================================================ */
    public function saveSSPIN(Request $request)
    {
        try {
            $user = auth('customer')->user();

            if (!$user) {
                Log::warning('SSPIN save blocked: unauthenticated');
                return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
            }

            $request->validate([
                'sspin' => 'required|digits:6'
            ]);

            Log::info("Saving SSPIN for user {$user->id}: {$request->sspin}");

            $user->sspin = $request->sspin;
            $user->save();

            return response()->json([
                'success' => true,
                'sspin'   => $user->sspin,
                'message' => 'SSPIN updated successfully.'
            ]);

        } catch (\Throwable $e) {
            Log::error('SSPIN save error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    /* ============================================================
     | SSPIN — SAVE USER-ENTERED PIN
     * ============================================================ */
    public function saveSupportPin(Request $request)
    {
        $request->validate(['sspin' => ['required', 'digits:6']]);

        $user = auth('customer')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated',
            ], 401);
        }

        $user->sspin = $request->input('sspin');
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Support PIN saved.',
            'sspin'   => $user->sspin,
        ]);
    }


            /* ============================================================
        | 🔥 FIXED — PASSWORD RESET REQUEST
        * ============================================================ */
        public function requestPasswordReset(Request $request)
        {
            $user = auth('customer')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

            try {

                // IMPORTANT — correct broker name
                $status = Password::broker('crm_users')->sendResetLink([
                    'email' => $user->email
                ]);

                if ($status !== Password::RESET_LINK_SENT) {
                    return response()->json([
                        'success' => false,
                        'message' => __($status)
                    ], 400);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Password reset link sent.'
                ]);

            } catch (\Throwable $e) {

                Log::error("Password reset request error: {$e->getMessage()}");

                return response()->json([
                    'success' => false,
                    'message' => 'Could not send password reset email.'
                ], 500);
            }
        }


}
