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
     * ---------------------------------------------------------------------
     * ENABLE 2FA FROM PORTAL — SEND CODE
     * ---------------------------------------------------------------------
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
        ]);
    }

    /**
     * ---------------------------------------------------------------------
     * ENABLE 2FA FROM PORTAL — VERIFY CODE
     * ---------------------------------------------------------------------
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
            ->orderByDesc('id')
            ->first();

        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired code.',
            ]);
        }

        $user->two_factor_email_enabled = 1;
        $user->two_factor_confirmed_at = now();
        $user->save();

        DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * ---------------------------------------------------------------------
     * LOGIN PAGE — SEND CODE AFTER PASSWORD SUCCESS
     * ---------------------------------------------------------------------
     */
    public function sendLogin2FACode(Request $request)
    {
        $userId = session('2fa_user_id');
        $user   = User::find($userId);

        if (! $user) {
            Log::warning("2FA SEND CODE FAILED — no user for id {$userId}");
            return response()->json([
                'success' => false,
                'message' => '2FA session expired.',
                'debug'   => ['user_id' => $userId],
            ]);
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
     * ---------------------------------------------------------------------
     * LOGIN PAGE — VERIFY ENTERED CODE
     * ---------------------------------------------------------------------
     */
    public function verifyLogin2FACode(Request $request)
    {
        Log::info('VERIFY LOGIN 2FA REQUEST', [
            'posted_code' => $request->code ?? 'NO CODE',
            'session_2fa_user_id' => session('2fa_user_id'),
        ]);

        $userId = session('2fa_user_id');

        if (! $userId) {
            Log::warning("2FA FAILED — Missing session user id");
            return response()->json([
                'success' => false,
                'message' => '2FA session expired. Please log in again.',
                'debug'   => ['missing_session_user_id' => true],
            ]);
        }

        $user = User::find($userId);

        if (! $user) {
            Log::warning("2FA FAILED — user not found for id {$userId}");
            return response()->json([
                'success' => false,
                'message' => '2FA session expired.',
                'debug'   => ['user_found' => false],
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
            Log::warning("2FA FAILED — token not found", [
                'user_id' => $user->id,
                'hash'    => $hash,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired code.',
                'debug'   => [
                    'user_id' => $user->id,
                    'token_valid' => false,
                ],
            ]);
        }

        // SUCCESS — log user in
        auth('customer')->login($user);

        DB::connection('crm')->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->delete();

        Log::info("2FA LOGIN SUCCESS for {$user->email}");

        return response()->json([
            'success' => true,
            'redirect' => route('customer.portal'),
            'debug' => ['final_login' => true],
        ]);
    }
}
