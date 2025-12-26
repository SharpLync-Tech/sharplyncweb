<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;

class RegisterController extends Controller
{
    /**
     * Simple file logger to /home/site/wwwroot/sharpfleet-registration.log
     */
    private function logRegistrationEvent(string $message): void
    {
        try {
            $file = base_path('sharpfleet-registration.log'); // plain text file in project root
            $line = '[' . now() . '] ' . $message . PHP_EOL;
            @file_put_contents($file, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            \Log::warning('sharpfleet-registration.log write failed: ' . $e->getMessage());
        }
    }

    public function showRegistrationForm()
    {
        return view('sharpfleet.admin.register');
    }

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
            if (DB::connection('sharpfleet')->table('users')->where('email', $validated['email'])->exists()) {
                $this->logRegistrationEvent("REGISTER BLOCKED (duplicate) → {$validated['email']}");
                return back()->withErrors([
                    'email' => 'That email is already registered. Please log in or use another email address.'
                ])->withInput();
            }

            // 3) Generate verification token
            $token = bin2hex(random_bytes(32));

            // 4) Create user WITH token + expiry
            $userId = DB::connection('sharpfleet')
                ->table('users')
                ->insertGetId([
                    'first_name'              => $validated['first_name'],
                    'last_name'               => $validated['last_name'],
                    'email'                   => $validated['email'],
                    'account_status'          => 'pending',
                    'activation_token'        => $token,
                    'activation_expires_at'   => Carbon::now()->addHour(),
                    'created_at'              => Carbon::now(),
                    'updated_at'              => Carbon::now(),
                ]);

            // Log DB row creation
            $this->logRegistrationEvent("REGISTERED → id={$userId} email={$validated['email']} token={$token}");

            // 5) Send verification email
            $verifyUrl = url('/app/sharpfleet/activate/' . $token);
            Mail::to($validated['email'])->send(new \App\Mail\SharpFleet\AccountActivation((object)[
                'id' => $userId,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'activation_token' => $token
            ]));

            // Log what we sent
            $this->logRegistrationEvent("EMAIL SENT → {$validated['email']} | URL={$verifyUrl}");

            return redirect('/app/sharpfleet/register/success')
                ->with('status', 'Verification email sent! Please check your inbox.');
        } catch (\Exception $e) {
            // Failure logs
            $this->logRegistrationEvent('REGISTER FAILED → ' . $e->getMessage());

            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
        }
    }

    public function showSuccess()
    {
        return view('sharpfleet.admin.register-success');
    }

    public function activate($token)
    {
        $user = DB::connection('sharpfleet')
            ->table('users')
            ->where('activation_token', $token)
            ->where('activation_expires_at', '>', Carbon::now())
            ->first();

        if (!$user) {
            return redirect('/app/sharpfleet/admin/register')->withErrors(['error' => 'Invalid or expired activation link.']);
        }

        // Guard against already activated users
        if ($user->account_status !== 'pending') {
            return redirect('/app/sharpfleet/admin/login')->withErrors(['error' => 'Account already activated.']);
        }

        // Log access
        $this->logRegistrationEvent("ACTIVATION FORM SHOWN → id={$user->id} email={$user->email}");

        return view('sharpfleet.admin.activate-account', [
            'first_name' => $user->first_name,
            'token' => $token
        ]);
    }

    public function completeRegistration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string'],
            'business_type' => ['required', 'in:sole_trader,company'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = DB::connection('sharpfleet')
            ->table('users')
            ->where('activation_token', $request->token)
            ->where('activation_expires_at', '>', Carbon::now())
            ->first();

        if (!$user) {
            return back()->withErrors(['error' => 'Invalid or expired activation token.']);
        }

        try {
            DB::connection('sharpfleet')->beginTransaction();

            // Create organisation
            $organisationId = DB::connection('sharpfleet')
                ->table('organisations')
                ->insertGetId([
                    'name' => $request->business_type === 'sole_trader' ? $user->first_name . ' ' . $user->last_name : 'Company',
                    'business_type' => $request->business_type,
                    'trial_ends_at' => Carbon::now()->addDays(30),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

            // Update user with organisation, password, and activate account
            DB::connection('sharpfleet')
                ->table('users')
                ->where('id', $user->id)
                ->update([
                    'organisation_id' => $organisationId,
                    'password_hash' => Hash::make($request->password),
                    'role' => 'admin',
                    'account_status' => 'active',
                    'trial_ends_at' => Carbon::now()->addDays(30),
                    'activated_at' => Carbon::now(),
                    'activation_token' => null,
                    'activation_expires_at' => null,
                    'updated_at' => Carbon::now(),
                ]);

            DB::connection('sharpfleet')->commit();

            // Send welcome email
            Mail::to($user->email)->send(new \App\Mail\SharpFleet\WelcomeEmail($user->first_name));

            // Send notification to admin
            Mail::to('info@sharplync.com.au')->send(new \App\Mail\SharpFleet\NewSubscriberNotification($user->email, $request->business_type));

            // Log success
            $this->logRegistrationEvent("ACCOUNT ACTIVATED → id={$user->id} email={$user->email}");

            // Log the user in
            session([
                'sharpfleet.user' => [
                    'id' => $user->id,
                    'organisation_id' => $organisationId,
                    'email' => $user->email,
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                    'role' => 'admin',
                    'logged_in' => true,
                ]
            ]);

            return redirect('/app/sharpfleet/admin')
                ->with('success', 'Welcome to SharpFleet! Your 30-day trial has started.');

        } catch (\Exception $e) {
            DB::connection('sharpfleet')->rollBack();
            $this->logRegistrationEvent("ACTIVATION FAILED → id={$user->id} | " . $e->getMessage());
            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
        }
    }
}