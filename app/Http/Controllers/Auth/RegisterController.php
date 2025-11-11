<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\CRM\User;
use App\Models\CRM\RegistrationLog;
use App\Mail\VerifyEmail;
use Exception;
use Illuminate\Validation\ValidationException; // ← Add this import for specific catch

class RegisterController extends Controller
{
    /**
     * Simple file logger to /home/site/wwwroot/registration.log
     */
    private function logRegistrationEvent(string $message): void
    {
        try {
            $file = base_path('registration.log'); // plain text file in project root
            $line = '[' . now() . '] ' . $message . PHP_EOL;
            @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            \Log::warning('registration.log write failed: ' . $e->getMessage());
        }
    }

    /**
     * Show the registration form (GET /register)
     */
    public function showRegistrationForm()
    {
        return view('customers.register');
    }

    /**
     * Handle registration form submission (POST /register)
     */
    public function register(Request $request)
    {
        try {
            // 1) Validate input
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name'  => 'required|string|max:255',
                'email'      => 'required|string|email|max:255',
            ]);

            // 2) Prevent duplicates
            if (User::where('email', $validated['email'])->exists()) {
                $this->logRegistrationEvent("REGISTER BLOCKED (duplicate) → {$validated['email']}");
                return back()->withErrors([
                    'email' => 'That email is already registered. Please log in or use another email address.'
                ])->withInput();
            }

            // 3) Generate verification token (this was missing → caused null token)
            $token = bin2hex(random_bytes(32));

            // 4) Create user WITH token + expiry
            $user = User::create([
                'first_name'              => $validated['first_name'],
                'last_name'               => $validated['last_name'],
                'email'                   => $validated['email'],
                'auth_provider'           => 'local',
                'account_status'          => 'pending',
                'verification_token'      => $token,
                'verification_expires_at' => Carbon::now()->addHour(),
            ]);

            // Log DB row creation
            $this->logRegistrationEvent("REGISTERED → id={$user->id} email={$user->email} token={$token}");

            // Legacy DB registration log (unchanged)
            RegistrationLog::create([
                'ip_address' => $request->ip(),
                'email'      => $request->email,
                'user_agent' => $request->userAgent(),
                'status'     => 'success',
            ]);

            // 5) Send verification email (Mailable builds URL from user->verification_token)
            $verifyUrl = url('/verify/' . $user->verification_token);
            Mail::to($user->email)->send(new VerifyEmail($user));

            // Log what we sent
            $this->logRegistrationEvent("EMAIL SENT → {$user->email} | URL={$verifyUrl}");

            return back()->with('status', 'Verification email sent! Please check your inbox.');
        } catch (Exception $e) {
            // Failure logs
            $this->logRegistrationEvent('REGISTER FAILED → ' . $e->getMessage());

            RegistrationLog::create([
                'ip_address' => $request->ip(),
                'email'      => $request->email ?? 'unknown',
                'user_agent' => $request->userAgent(),
                'status'     => 'failed',
                'reason'     => $e->getMessage(),
            ]);

            return back()->withErrors([
                'error' => 'Registration failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the password setup form (GET /set-password/{id})
     */
    public function showPasswordForm($id)
    {
        try {
            $user = User::findOrFail($id); // Fetch by ID; 404 if not found

            // Optional: Guard against unverified/invalid users
            if ($user->account_status !== 'verified') {
                return redirect()->route('register')->withErrors(['error' => 'Account not ready for password setup.']);
            }

            // Log access (optional, using your logger)
            $this->logRegistrationEvent("PASSWORD FORM SHOWN → id={$id} email={$user->email}");

            return view('customers.set-password', compact('user')); // Matches your blade's $user expectation
        } catch (\Exception $e) {
            $this->logRegistrationEvent("PASSWORD FORM FAILED → id={$id} | " . $e->getMessage());
            return redirect()->route('register')->withErrors(['error' => 'Invalid setup link. Please register again.']);
        }
    }

    /**
     * Save the new password (POST /set-password/{id})
     */
    public function savePassword(Request $request, $id)
    {
        try {
            $request->validate([
                'password' => ['required', 'string', 'min:8', 'confirmed'], // Add rules as needed (e.g., regex for strength)
            ]);

            $user = User::findOrFail($id);

            // Guard: Ensure still verified and no password yet
            if ($user->account_status !== 'verified' || !empty($user->password)) {
                return redirect()->route('register')->withErrors(['error' => 'Password already set or invalid setup.']);
            }

            // TEMP DEBUG: Log pre-update state
            $this->logRegistrationEvent("PRE-UPDATE → id={$id} status={$user->account_status} password_empty=" . (empty($user->password) ? 'yes' : 'no'));

            // Hash and save (combine into one update for efficiency)
            $user->update([
                'password' => Hash::make($request->password),
                'account_status' => 'active',
                'email_verified_at' => now(),
                'verification_token' => null,
                'verification_expires_at' => null,
            ]);

            // Log success
            $this->logRegistrationEvent("PASSWORD SAVED → id={$id} email={$user->email}");

            // Redirect to login/dashboard (adjust as needed)
            return redirect()->route('customer.login')->with('status', 'Password set! Please log in.');

        } catch (ValidationException $e) {
            // Validation fails: Back to form with errors
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            $this->logRegistrationEvent("PASSWORD SAVE FAILED → id={$id} | " . $e->getMessage());
            
            // TEMP DEBUG: Flash the real error message (remove 'Try again.' in prod)
            $debugError = 'Failed to save password: ' . $e->getMessage() . ' (Check server logs for details)';
            return back()->withErrors(['error' => $debugError]);
        }
    }
}