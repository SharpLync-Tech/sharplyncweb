<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\TwoFactorEmailCode;

class SecurityController extends Controller
{
    /**
     * ======================================================
     *  PORTAL 2FA — SEND EMAIL CODE (When enabling 2FA)
     * ======================================================
     */
    public function sendEmail2FACode(Request $request)
    {
        $user = auth()->user();

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
            'message' => 'Verification code sent.',
        ]);
    }


    /**
     * ======================================================
     *  PORTAL 2FA — VERIFY EMAIL CODE (Enabling 2FA)
     * ======================================================
     */
    public function verifyEmail2FACode(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
        ]);

        $user   = auth()->user();
        $hashed = hash('sha256', $request->code);

        $record = DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->where('token_hash', $hashed)
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired code.',
            ], 422);
        }

        // Enable 2FA
        $user->two_factor_enabled       = true;
        $user->two_factor_method        = 'email';
        $user->two_factor_confirmed_at  = now();
        $user->save();

        DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Email authentication enabled.',
        ]);
    }


    /**
     * ======================================================
     *  LOGIN 2FA — SEND LOGIN CODE
     * ======================================================
     */
    public function sendLogin2FACode(Request $request)
    {
        $userId = session('2fa:user:id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please login again.'
            ], 419);
        }

        $user = User::find($userId);

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


    /**
     * ======================================================
     *  LOGIN 2FA — VERIFY LOGIN CODE
     * ======================================================
     */
    public function verifyLogin2FACode(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
        ]);

        $userId = session('2fa:user:id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please login again.'
            ], 419);
        }

        $hashed = hash('sha256', $request->code);

        $record = DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $userId)
            ->where('channel', 'email')
            ->where('token_hash', $hashed)
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired code.'
            ], 422);
        }

        // Clean up old codes
        DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $userId)
            ->where('channel', 'email')
            ->delete();

        // Log user in
        Auth::guard('customer')->loginUsingId($userId);

        // Clear temporary session
        session()->forget(['2fa:user:id', '2fa:method']);

        return response()->json([
            'success'   => true,
            'redirect'  => route('customer.portal'),
        ]);
    }
}
