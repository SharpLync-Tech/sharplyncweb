<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use App\Models\CRM\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ResetPasswordController extends Controller
{
    /**
     * Show the existing "Set Password" page (Option A)
     */
    public function showResetForm(Request $request, $token)
    {
        $email = $request->query('email');

        if (!$email) {
            abort(403, 'Invalid password reset link.');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            abort(403, 'Invalid password reset link.');
        }

        // Hash the raw token to compare with DB value
        $hashed = hash('sha256', $token);

        // Validate token + expiry
        if (
            !$user->password_reset_token ||
            $user->password_reset_token !== $hashed ||
            !$user->password_reset_expires_at ||
            Carbon::parse($user->password_reset_expires_at)->isPast()
        ) {
            abort(403, 'Invalid or expired password reset link.');
        }

        // Pass to the SAME "Set Password" blade used in registration
        return view('customers.passwords.reset', [
            'token' => $token,
            'email' => $email,
            'mode'  => 'reset', // tells the blade this is NOT registration
        ]);
    }

    /**
     * Handle the password update
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'token'    => ['required'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'Invalid reset attempt.');
        }

        // Compare hashed tokens
        $hashed = hash('sha256', $request->token);

        if ($user->password_reset_token !== $hashed) {
            return back()->with('error', 'Invalid reset token.');
        }

        if (!$user->password_reset_expires_at ||
            Carbon::parse($user->password_reset_expires_at)->isPast()) {
            return back()->with('error', 'Password reset link expired.');
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->password_reset_token = null;
        $user->password_reset_expires_at = null;
        $user->save();

        return redirect()->route('customer.login')
            ->with('status', 'Password reset! Please log in.');
    }
}
