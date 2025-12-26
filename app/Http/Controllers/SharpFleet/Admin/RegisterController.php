<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('sharpfleet.admin.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organisation_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:sharpfleet.users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'billing_plan' => ['required', 'in:starter,professional,enterprise'],
            'agree_terms' => ['required', 'accepted'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::connection('sharpfleet')->beginTransaction();

            // Create organisation
            $organisationId = DB::connection('sharpfleet')
                ->table('organisations')
                ->insertGetId([
                    'name' => $request->organisation_name,
                    'billing_plan' => $request->billing_plan,
                    'trial_ends_at' => Carbon::now()->addDays(30),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

            // Create admin user
            $userId = DB::connection('sharpfleet')
                ->table('users')
                ->insertGetId([
                    'organisation_id' => $organisationId,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'admin',
                    'is_active' => 1,
                    'trial_ends_at' => Carbon::now()->addDays(30),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

            DB::connection('sharpfleet')->commit();

            // Log the user in
            session([
                'sharpfleet.user' => [
                    'id' => $userId,
                    'organisation_id' => $organisationId,
                    'email' => $request->email,
                    'name' => trim($request->first_name . ' ' . $request->last_name),
                    'role' => 'admin',
                    'logged_in' => true,
                ],
                'sharpfleet.trial_started' => Carbon::now()->toDateTimeString(),
            ]);

            return redirect('/app/sharpfleet/admin')
                ->with('success', 'Welcome to SharpFleet! Your 30-day trial has started.');

        } catch (\Exception $e) {
            DB::connection('sharpfleet')->rollBack();
            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
        }
    }
}