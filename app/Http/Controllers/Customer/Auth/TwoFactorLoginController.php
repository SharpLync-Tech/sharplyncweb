<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\CRM\User;
use App\Mail\TwoFactorEmailCode;

class TwoFactorLoginController extends Controller
{
    /**
     * Send login-time 2FA code
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

        // generate 6-digit code
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

        // complete login
        Auth::guard('customer')->login($user);

        session()->forget('2fa_user_id');
        session()->forget('email_masked');
        session()->forget('show_2fa_modal');

        return response()->json([
            'success' => true,
            'redirect' => url('/portal')
        ]);
    }
}
