<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\CRM\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class MicrosoftCustomerController extends Controller
{
    /**
     * Redirect the customer to Microsoft's OAuth page.
     */
    /**
     * Redirect the customer to Microsoft's OAuth page.
     */
    public function redirect()
    {
        // CRITICAL: Use the 'azure' driver for the socialiteproviders/microsoft-azure package
        return \Laravel\Socialite\Facades\Socialite::driver('azure')
            // Set HTTP client timeout to prevent Azure App Service 504s
            ->setHttpClient(new \GuzzleHttp\Client(['timeout' => 10]))
            // Requesting necessary scopes for Azure AD
            ->scopes(['openid', 'profile', 'email'])
            // Optional: explicitly set redirect URL (helps on some Azure configs)
            ->redirectUrl(config('services.microsoft_customer.redirect'))
            ->redirect();
    }


    /**
     * Handle callback from Microsoft and authenticate or create user.
     */
    public function callback()
    {
        try {
            // CRITICAL: Ensure the driver is 'azure'
            $msUser = Socialite::driver('azure')->user();

            $user = User::on('crm')->updateOrCreate(
                [
                    'email' => $msUser->getEmail(),
                ],
                [
                    'first_name' => explode(' ', $msUser->getName())[0] ?? null,
                    'last_name'  => explode(' ', $msUser->getName())[1] ?? null,
                    'auth_provider' => 'microsoft',
                    'provider_id' => $msUser->getId(),
                    'email_verified_at' => Carbon::now(),
                    'account_status' => 'verified',
                ]
            );

            // CRITICAL: Use the confirmed 'customer' guard for login
            Auth::guard('customer')->login($user, true);

            // Redirect the user to profile setup route after successful login/registration
            return redirect()->route('profile.create')->with('status', 'Welcome back, ' . $user->first_name . '!');
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('customer.login')->with('error', 'Microsoft sign-in failed. Please try again.');
        }
    }
}