<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

        // Store token in DB
        DB::table('user_two_factor_tokens')->insert([
            'user_id'      => $user->id,
            'type'         => 'email',
            'token'        => $code,
            'expires_at'   => Carbon::now()->addMinutes(10),
            'created_at'   => now(),
            'updated_at'   => now(),
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

        $record = DB::table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->where('type', 'email')
            ->where('token', $request->code)
            ->where('expires_at', '>', Carbon::now())
            ->orderByDesc('id')
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired code.'
            ], 422);
        }

        // Mark email 2FA as active on the user
        $user->two_factor_enabled = true;
        $user->two_factor_method  = 'email';
        $user->two_factor_confirmed_at = now();
        $user->save();

        // Cleanup tokens
        DB::table('user_two_factor_tokens')
            ->where('user_id', $user->id)
            ->where('type', 'email')
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Email authentication enabled.'
        ]);
    }
}
