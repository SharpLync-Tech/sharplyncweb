<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\CRM\User;
use PragmaRX\Google2FA\Google2FA;

class LoginTotpController extends Controller
{
    /**
     * Verify a login-time TOTP (Authenticator App) code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $userId = session('2fa_user_id');
        $method = session('2fa_method');

        // Safety: session must exist
        if (!$userId || $method !== 'app') {
            Log::warning("LOGIN TOTP VERIFY: Missing session or wrong method", [
                'session_user'  => $userId,
                'session_method'=> $method,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Your login session expired. Please log in again.'
            ], 419);
        }

        /** @var User|null $user */
        $user = User::find($userId);

        if (!$user) {
            Log::error("LOGIN TOTP VERIFY: User not found", ['user_id' => $userId]);
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        if (!$user->two_factor_secret) {
            Log::error("LOGIN TOTP VERIFY: Secret missing", ['user_id' => $userId]);
            return response()->json([
                'success' => false,
                'message' => 'Authenticator not set up. Please enable it again.'
            ], 422);
        }

        // --------------------------------------------------------------
        // VERIFY TOTP
        // --------------------------------------------------------------
        $google2fa = new Google2FA();
        $code      = $request->code;

        // Allow a 1-step time window (±30 seconds)
        $window    = 1;

        $valid = $google2fa->verifyKey($user->two_factor_secret, $code, $window);

        if (!$valid) {
            Log::warning("LOGIN TOTP VERIFY FAILED", [
                'user_id' => $user->id,
                'code'    => $code,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired authentication code.'
            ], 422);
        }

        // --------------------------------------------------------------
        // SUCCESS → Finalize login
        // --------------------------------------------------------------
        Auth::guard('customer')->login($user);

        // Clean up session
        session()->forget('2fa_user_id');
        session()->forget('2fa_method');
        session()->forget('show_app_2fa_modal');

        $user->update([
            'last_login_at' => now()
        ]);

        Log::info("LOGIN TOTP SUCCESS", ['user_id' => $user->id]);

        return response()->json([
            'success'  => true,
            'redirect' => url('/portal'),
        ]);
    }
}
