<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use App\Models\CRM\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /**
     * Show the password reset form.
     */
    public function showResetForm(Request $request, string $token)
    {
        // Validate signed URL (security)
        if (!$request->hasValidSignatureWhileIgnoring(['email'])) {
            return redirect()
                ->route('customer.password.request')
                ->with('error', 'This password reset link is invalid or has expired.');
        }

        $email = $request->query('email');

        return view('customers.passwords.reset', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Actually reset the customer's password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'token'    => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        /** @var \App\Models\CRM\User|null $user */
        $user = User::where('email', $request->email)->first();

        // If user not found or no reset token stored
        if (!$user || !$user->password_reset_token || !$user->password_reset_expires_at) {
            return back()->with('error', 'This password reset link is invalid or has expired.');
        }

        // Check token expiry
        if (Carbon::parse($user->password_reset_expires_at)->isPast()) {
            return back()->with('error', 'This password reset link has expired. Please request a new one.');
        }

        // Check token match (hash)
        $incomingHashed = hash('sha256', $request->token);
        if (!hash_equals($user->password_reset_token, $incomingHashed)) {
            return back()->with('error', 'This password reset link is invalid. Please request a new one.');
        }

        // All good — update password
        $user->password = Hash::make($request->password);
        $user->password_reset_token = null;
        $user->password_reset_expires_at = null;
        $user->save();

        // Redirect user to login with confirmation
        return redirect()
            ->route('customer.login')
            ->with('status', 'Your password has been reset. You can now log in.');
    }
}
