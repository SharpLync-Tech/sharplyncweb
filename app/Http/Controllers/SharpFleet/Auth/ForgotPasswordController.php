<?php

namespace App\Http\Controllers\SharpFleet\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SharpFleet\PasswordReset;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class ForgotPasswordController extends Controller
{
    private function logPasswordResetEvent(string $message): void
    {
        try {
            $file = base_path('sharpfleet-password-resets.log');
            $line = '[' . now() . '] ' . $message . PHP_EOL;
            @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            \Log::warning('sharpfleet-password-resets.log write failed: ' . $e->getMessage());
        }
    }

    public function showLinkRequestForm()
    {
        return view('sharpfleet.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Always show the same success message, even if email isn't found
        $genericStatus = 'If that email exists in our system, a reset link has been sent.';

        $email = (string) $request->email;

        // Best-effort cleanup of expired tokens
        try {
            if (Schema::connection('sharpfleet')->hasTable('password_resets')) {
                DB::connection('sharpfleet')
                    ->table('password_resets')
                    ->where('expires_at', '<=', Carbon::now())
                    ->delete();
            }
        } catch (\Throwable $e) {
            $this->logPasswordResetEvent('CLEANUP FAILED → ' . $e->getMessage());
        }

        // Look up user in SharpFleet DB
        $user = DB::connection('sharpfleet')
            ->table('users')
            ->where('email', $email)
            ->first();

        // Log request (do not block user flow on logging)
        $this->logPasswordResetEvent('RESET REQUESTED → email=' . $email . ' found=' . ($user ? 'yes' : 'no'));

        if (!$user) {
            return redirect('/app/sharpfleet/login')->with('status', $genericStatus);
        }

        // If the table isn't present yet, don't leak details to the user. Log for admins.
        if (!Schema::connection('sharpfleet')->hasTable('password_resets')) {
            $this->logPasswordResetEvent('RESET BLOCKED (missing table password_resets) → email=' . $email);
            return redirect('/app/sharpfleet/login')->with('status', $genericStatus);
        }

        // Generate and store reset token (store only a hash)
        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = Carbon::now()->addMinutes(30);

        try {
            // One active token per email: remove previous tokens for this email
            DB::connection('sharpfleet')
                ->table('password_resets')
                ->where('email', $email)
                ->delete();

            DB::connection('sharpfleet')
                ->table('password_resets')
                ->insert([
                    'email' => $email,
                    'token_hash' => $tokenHash,
                    'expires_at' => $expiresAt,
                    'used_at' => null,
                    'created_at' => Carbon::now(),
                ]);

            $resetUrl = url('/app/sharpfleet/password/reset/' . $rawToken);

            Mail::to($email)->send(new PasswordReset((object) [
                'email' => $email,
                'first_name' => $user->first_name ?? null,
                'reset_url' => $resetUrl,
                'expires_minutes' => 30,
            ]));

            $this->logPasswordResetEvent('RESET EMAIL SENT → email=' . $email . ' token_hash=' . $tokenHash . ' expires_at=' . $expiresAt);
        } catch (\Throwable $e) {
            $this->logPasswordResetEvent('RESET FAILED → email=' . $email . ' error=' . $e->getMessage());
            // Do not leak the failure to the user.
        }

        return redirect('/app/sharpfleet/login')->with('status', $genericStatus);
    }
}
