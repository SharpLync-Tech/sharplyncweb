<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecurityController extends Controller
{
    /**
     * Toggle Email-based 2FA for the logged-in customer.
     *
     * Request: JSON { enabled: true|false }
     * Response: JSON with current 2FA state.
     */
    public function toggleEmail(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $user = Auth::user(); // same user you use in the portal

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $enabled = (bool) $request->input('enabled');

        // Flip the flag on the users table
        $user->two_factor_email_enabled = $enabled;

        // Keep default method sensible
        if ($enabled) {
            if (!in_array($user->two_factor_default_method, ['email', 'app', 'sms'], true)) {
                $user->two_factor_default_method = 'email';
            }
        } else {
            if ($user->two_factor_default_method === 'email') {
                $user->two_factor_default_method = null;
            }
        }

        $user->save();

        return response()->json([
            'status'         => 'ok',
            'email_enabled'  => (bool) $user->two_factor_email_enabled,
            'default_method' => $user->two_factor_default_method,
        ]);
    }
}