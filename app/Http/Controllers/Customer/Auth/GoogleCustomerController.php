<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\CRM\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class GoogleCustomerController extends Controller
{
    /**
     * Redirect the customer to Google's OAuth page.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback from Google and authenticate or create user.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::on('crm')->updateOrCreate(
                [
                    'email' => $googleUser->getEmail(),
                ],
                [
                    'first_name' => explode(' ', $googleUser->getName())[0] ?? null,
                    'last_name'  => explode(' ', $googleUser->getName())[1] ?? null,
                    'auth_provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'email_verified_at' => Carbon::now(),
                    'account_status' => 'verified',
                ]
            );

            // CRITICAL: Use the explicit 'customer' guard
            Auth::guard('customer')->login($user, true);

            // Redirect the user to profile setup route after successful login/registration
            return redirect()->route('profile.create')->with('status', 'Welcome back, ' . $user->first_name . '!');
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('customer.login')->with('error', 'Google sign-in failed. Please try again.');
        }
    }
}