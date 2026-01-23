<?php

namespace App\Http\Controllers\SharpFleet\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use App\Services\SharpFleet\MobileTokenService;
use App\Services\SharpFleet\AuditLogService;

class ResetPasswordController extends Controller
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

    public function showResetForm(Request $request, string $token)
    {
        if (!Schema::connection('sharpfleet')->hasTable('password_resets')) {
            $this->logPasswordResetEvent('RESET FORM BLOCKED (missing table password_resets)');
            return redirect('/app/sharpfleet/password/forgot')->with('error', 'Invalid or expired password reset link.');
        }

        $tokenHash = hash('sha256', $token);

        $reset = DB::connection('sharpfleet')
            ->table('password_resets')
            ->where('token_hash', $tokenHash)
            ->whereNull('used_at')
            ->first();

        if (!$reset) {
            return redirect('/app/sharpfleet/password/forgot')->with('error', 'Invalid or expired password reset link.');
        }

        if (Carbon::parse($reset->expires_at)->isPast()) {
            return redirect('/app/sharpfleet/password/forgot')->with('error', 'Invalid or expired password reset link.');
        }

        return view('sharpfleet.passwords.reset', [
            'token' => $token,
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        if (!Schema::connection('sharpfleet')->hasTable('password_resets')) {
            $this->logPasswordResetEvent('RESET SUBMIT BLOCKED (missing table password_resets)');
            return back()->with('error', 'Invalid reset attempt.');
        }

        $tokenHash = hash('sha256', (string) $request->token);

        $reset = DB::connection('sharpfleet')
            ->table('password_resets')
            ->where('token_hash', $tokenHash)
            ->whereNull('used_at')
            ->first();

        if (!$reset) {
            return back()->with('error', 'Invalid reset token.');
        }

        if (Carbon::parse($reset->expires_at)->isPast()) {
            return back()->with('error', 'Password reset link expired.');
        }

        $email = (string) $reset->email;

        $user = DB::connection('sharpfleet')
            ->table('users')
            ->where('email', $email)
            ->first();

        if (!$user) {
            $this->logPasswordResetEvent('RESET FAILED (user missing) → email=' . $email . ' token_hash=' . $tokenHash);
            return back()->with('error', 'Invalid reset attempt.');
        }

        try {
            DB::connection('sharpfleet')->beginTransaction();

            DB::connection('sharpfleet')
                ->table('users')
                ->where('id', $user->id)
                ->update([
                    'password_hash' => Hash::make($request->password),
                    'updated_at' => Carbon::now(),
                ]);

            DB::connection('sharpfleet')
                ->table('password_resets')
                ->where('id', $reset->id)
                ->update([
                    'used_at' => Carbon::now(),
                ]);

            DB::connection('sharpfleet')->commit();

            if (isset($user->organisation_id)) {
                $revoked = (new MobileTokenService())->revokeTokensForUser(
                    (int) $user->organisation_id,
                    (int) $user->id,
                    'password_reset'
                );

                if ((int) ($revoked['count'] ?? 0) > 0) {
                    (new AuditLogService())->logSystem($request, (int) $user->organisation_id, 'mobile_token_revoked', [
                        'target_user_id' => (int) $user->id,
                        'revoke_reason' => 'password_reset',
                        'token_count_revoked' => (int) ($revoked['count'] ?? 0),
                        'device_ids' => $revoked['device_ids'] ?? [],
                    ]);
                }
            }

            $this->logPasswordResetEvent('PASSWORD RESET SUCCESS → email=' . $email . ' user_id=' . $user->id);

            return redirect('/app/sharpfleet/login')->with('status', 'Password reset! Please log in.');
        } catch (\Throwable $e) {
            DB::connection('sharpfleet')->rollBack();
            $this->logPasswordResetEvent('PASSWORD RESET FAILED → email=' . $email . ' error=' . $e->getMessage());
            return back()->with('error', 'Password reset failed. Please try again.');
        }
    }
}
