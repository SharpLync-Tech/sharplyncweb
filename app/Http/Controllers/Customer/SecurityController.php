<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\CRM\User as CrmUser;
use App\Mail\TwoFactorEmailCode;

class SecurityController extends Controller
{
    /**
     * PORTAL: Send verification code to enable Email 2FA
     */
    public function sendEmail2FACode(Request $request)
    {
        /** @var \App\Models\CRM\User $user */
        $user = Auth::guard('customer')->user();

        // 6-digit code
        $code = rand(100000, 999999);
        $hash = hash('sha256', $code);

        // CRM DB token
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
            'message' => 'Verification code sent.',
        ]);
    }

    /**
     * PORTAL: Verify code & enable Email 2FA
     */
    public function verifyEmail2FACode(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
        ]);

        /** @var \App\Models\CRM\User $user */
        $user = Auth::guard('customer')->user();

        $hashed = hash('sha256', $request->code);

        $record = DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->where('token_hash', $hashed)
            ->where('expires_at', '>', now())
            ->whereNull('consumed_at')
            ->orderByDesc('id')
            ->first();

        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired code.',
            ], 422);
        }

        // Enable email 2FA on CRM user
        $user->two_factor_email_enabled = 1;
        $user->two_factor_confirmed_at  = now();
        $user->save(); // CRM connection

        // Mark token consumed / clean up
        DB::connection('crm')->table('user_two_factor_tokens')
            ->where('id', $record->id)
            ->update(['consumed_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Email authentication enabled.',
        ]);
    }

    /**
     * LOGIN: Send (or resend) 2FA code AFTER password verification.
     * Called from the login modal "Resend Code" button.
     */
    public function sendLogin2FACode(Request $request)
    {
        $userId = $request->session()->get('2fa_user_id');

        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => 'No 2FA session found.',
            ], 401);
        }

        /** @var \App\Models\CRM\User|null $user */
        $user = CrmUser::find($userId);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // 6-digit code
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
            'message' => 'Login verification code sent.',
        ]);
    }

    /**
     * LOGIN: Verify 2FA code & complete login
     */
    public function verifyLogin2FACode(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
        ]);

        $userId = $request->session()->get('2fa_user_id');

        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => '2FA session expired. Please log in again.',
            ], 401);
        }

        /** @var \App\Models\CRM\User|null $user */
        $user = CrmUser::find($userId);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $hashed = hash('sha256', $request->code);

        $record = DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->where('token_hash', $hashed)
            ->where('expires_at', '>', now())
            ->whereNull('consumed_at')
            ->orderByDesc('id')
            ->first();

        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired code.',
            ], 422);
        }

        // Mark token consumed
        DB::connection('crm')->table('user_two_factor_tokens')
            ->where('id', $record->id)
            ->update(['consumed_at' => now()]);

        // Complete login now that 2FA passed
        Auth::guard('customer')->login($user);

        // Clear 2FA session data
        $request->session()->forget('2fa_user_id');
        $request->session()->forget('show_2fa_modal');

        // Redirect target (default: /portal)
        $redirect = url('/portal');

        return response()->json([
            'success'  => true,
            'message'  => '2FA verification successful.',
            'redirect' => $redirect,
        ]);
    }

    /**
     * Placeholder for toggleEmail (for future "turn off 2FA" logic).
     */
    public function toggleEmail(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Toggle endpoint not yet implemented.',
        ], 501);
    }
}
