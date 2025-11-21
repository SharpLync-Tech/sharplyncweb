<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use App\Models\CRM\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Show the "forgot password" form
     */
    public function showLinkRequestForm()
    {
        return view('customers.passwords.email');
    }

    /**
     * Send reset email + redirect to login (Option B)
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Always show the same success message, even if email isn't found
        $genericStatus = 'If that email exists in our system, a reset link has been sent.';

        /** @var \App\Models\CRM\User|null $user */
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()
                ->route('customer.login')
                ->with('status', $genericStatus);
        }

        // Generate and store reset token
        $rawToken = Str::random(64);
        $hashedToken = hash('sha256', $rawToken);

        $user->password_reset_token = $hashedToken;
        $user->password_reset_expires_at = Carbon::now()->addMinutes(60);
        $user->save();

        // Signed reset URL
        $resetUrl = URL::temporarySignedRoute(
            'customer.password.reset.form',
            Carbon::now()->addMinutes(60),
            [
                'token' => $rawToken,
                'email' => $user->email,
            ]
        );

        // Send the email
        Mail::send(
            'emails.customers.password-reset',
            [
                'resetUrl' => $resetUrl,
                'user' => $user,
            ],
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Reset your SharpLync Customer Portal password');
            }
        );

        // Security-friendly redirect
        return redirect()
            ->route('customer.login')
            ->with('status', $genericStatus);
    }
}
