<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\TwoFactorEmailCode;

class SecurityController extends Controller
{
    /**
     * Step 1 — Send verification code to user's email
     */
    public function sendEmail2FACode(Request $request)
    {
        $user = auth()->user();

        // Generate a clean 6-digit code
        $code = rand(100000, 999999);

        // Hash token for DB storage
        $tokenHash = hash('sha256', $code);

        // Store token in CRM DB
        DB::connection('crm')
            ->table('user_two_factor_tokens')
            ->insert([
                'user_id'    => $user->id,
                'channel'    => 'email',
                'token_hash' => $tokenHash,
                'sent_to'    => $user->email,
                'expires_at' => now()->addMinutes(10),
                'created_at' => now(),
            ]);

        // Send email
        Mail::to($user->email)->send(new TwoFactorEmailCode($user, $code));

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent.'
        ]);
    }

    /**
     * Step 2 — Verify the email code
     */
    public function verifyEmail2FACode(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
        ]);

        $user = auth()->user();

        // Hash incoming code so it matches DB
        $hashed = hash('sha256', $request->code);

        // Look up token in CRM DB
        $record = DB::connection('crm')
            ->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
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

        // Mark email 2FA as active (CMS user table)
        $user->two_factor_enabled = true;
        $user->two_factor_method  = 'email';
        $user->two_factor_confirmed_at = now();
        $user->save();

        // Cleanup all email tokens for this user
        DB::connection('crm')
            ->table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Email authentication enabled.'
        ]);
    }
}
