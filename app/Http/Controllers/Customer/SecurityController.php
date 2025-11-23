<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\CRM\User;
use App\Mail\TwoFactorEmailCode;

class SecurityController extends Controller
{
    /**
     * =====================================================
     * PORTAL 2FA: SEND SETUP CODE
     * =====================================================
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

        return response()->json(['success' => true]);
    }


    /**
     * =====================================================
     * PORTAL 2FA: VERIFY SETUP CODE
     * =====================================================
     */
    public function verifyEmail2FACode(Request $request)
    {
        $request->validate(['code' => 'required|numeric']);

        $user = auth()->user();
        $hash = hash('sha256', $request->code);

        $record = DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->where('token_hash', $hash)
            ->where('expires_at', '>', now())
            ->first();

        if (! $record) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired code.'], 422);
        }

        // Enable 2FA
        $user->two_factor_email_enabled = 1;
        $user->two_factor_confirmed_at = now();
        $user->save();

        // Delete tokens
        DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->delete();

        return response()->json(['success' => true]);
    }


    /**
     * =====================================================
     * LOGIN-TIME 2FA: SEND LOGIN VERIFICATION CODE
     * =====================================================
     */
    public function sendLogin2FACode(Request $request)
    {
        $userId = session('2fa_user_id');

        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'No 2FA session.']);
        }

        $user = User::find($userId);

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Account not found.']);
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

        return response()->json(['success' => true]);
    }


    /**
     * =====================================================
     * LOGIN-TIME 2FA: VERIFY LOGIN CODE
     * =====================================================
     */
    public function verifyLogin2FACode(Request $request)
    {
        $request->validate(['code' => 'required|numeric']);

        $userId = session('2fa_user_id');

        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => '2FA session expired. Please log in again.'
            ]);
        }

        $user = User::find($userId);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found.'
            ]);
        }

        $hash = hash('sha256', $request->code);

        $record = DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->where('token_hash', $hash)
            ->where('expires_at', '>', now())
            ->first();

        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired code.'
            ]);
        }

        // CLEAN UP TOKEN
        DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->delete();

        // LOG USER IN
        auth('customer')->login($user);

        // REMOVE 2FA SESSION
        session()->forget(['2fa_user_id', 'email_masked', 'show_2fa_modal']);

        return response()->json([
            'success' => true,
            'redirect' => route('customer.portal')
        ]);
    }
}
