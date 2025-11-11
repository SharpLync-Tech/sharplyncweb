<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CRM\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class VerifyController extends Controller
{
    public function verify(string $token)
    {
        $ip = request()->ip();
        $url = request()->fullUrl();
        Log::info("[VERIFY CLICKED] {$url} from {$ip}");

        // Try to find the user by token
        $user = User::on('crm')->where('verification_token', $token)->first();

        // If no user found by token, try to find recently verified user (handles prefetch or double-click)
        if (!$user) {
            $recentUser = User::on('crm')
                ->where('account_status', 'verified')
                ->orderByDesc('updated_at')
                ->first();

            if ($recentUser) {
                Log::info("[VERIFY RECOVERED] id={$recentUser->id} email={$recentUser->email}");
                return redirect('/set-password/' . $recentUser->id)
                    ->with('status', 'Your email is already verified. You can now set your password.');
            }

            Log::warning("[VERIFY FAILED] no user found for token={$token}");
            return redirect('/set-password')->withErrors([
                'error' => 'We couldn’t verify your account. Please contact support if this persists.'
            ]);
        }

        // Check for expired link
        if ($user->verification_expires_at && Carbon::parse($user->verification_expires_at)->isPast()) {
            Log::warning("[VERIFY FAILED] expired token for email={$user->email}");
            return redirect('/set-password')->withErrors([
                'error' => 'Your verification link expired. Please register again.'
            ]);
        }

        // Mark user as verified
        $user->update([
            'email_verified_at'       => Carbon::now(),
            'account_status'          => 'verified',
            'verification_token'      => null,
            'verification_expires_at' => null,
        ]);

        Log::info("[VERIFY OK] id={$user->id} email={$user->email}");
        Log::info("[REDIRECT] /set-password/{$user->id}");

        // Always redirect to password creation
        return redirect('/set-password/' . $user->id)
            ->with('status', 'Email verified successfully! You can now set your password.');
    }
}