<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class DriverInviteController extends Controller
{
    public function showAcceptForm(string $token)
    {
        $user = DB::connection('sharpfleet')
            ->table('users')
            ->where('activation_token', $token)
            ->where('activation_expires_at', '>', Carbon::now())
            ->where('account_status', 'pending')
            ->where('role', 'driver')
            ->first();

        if (!$user) {
            return redirect('/app/sharpfleet/login')
                ->withErrors(['error' => 'Invalid or expired invitation link.']);
        }

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->select('id', 'name')
            ->where('id', (int) ($user->organisation_id ?? 0))
            ->first();

        return view('sharpfleet.admin.activate-driver', [
            'token' => $token,
            'email' => $user->email,
            'organisationName' => $organisation->name ?? null,
        ]);
    }

    public function complete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = DB::connection('sharpfleet')
            ->table('users')
            ->where('activation_token', $request->token)
            ->where('activation_expires_at', '>', Carbon::now())
            ->where('account_status', 'pending')
            ->where('role', 'driver')
            ->first();

        if (!$user) {
            return back()->withErrors(['error' => 'Invalid or expired invitation token.']);
        }

        $organisationId = (int) ($user->organisation_id ?? 0);
        if ($organisationId <= 0) {
            return back()->withErrors(['error' => 'This invitation is missing an organisation. Please ask your admin to resend it.']);
        }

        DB::connection('sharpfleet')
            ->table('users')
            ->where('id', $user->id)
            ->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'password_hash' => Hash::make($request->password),
                'role' => 'driver',
                'is_driver' => 1,
                'account_status' => 'active',
                'activated_at' => Carbon::now(),
                'activation_token' => null,
                'activation_expires_at' => null,
                'updated_at' => Carbon::now(),
            ]);

        // Log the user into SharpFleet
        $request->session()->regenerate();
        $request->session()->put('sharpfleet.user', [
            'id' => $user->id,
            'organisation_id' => $organisationId,
            'email' => $user->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => trim($request->first_name . ' ' . $request->last_name),
            'role' => 'driver',
            'is_driver' => 1,
            'logged_in' => true,
        ]);

        return redirect('/app/sharpfleet/driver')
            ->with('success', 'Welcome! Your driver account is ready.');
    }
}
