<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SharpFleet\DriverInvitation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DriverInviteController extends Controller
{
    public function create(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->select('id', 'name')
            ->where('id', $organisationId)
            ->first();

        return view('sharpfleet.admin.users.invite', [
            'organisation' => $organisation,
        ]);
    }

    public function store(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->select('id', 'name')
            ->where('id', $organisationId)
            ->first();

        if (!$organisation) {
            abort(403, 'Organisation not found.');
        }

        // Brand new users only, except allowing resends for a pending invite in THIS organisation.
        $existing = DB::connection('sharpfleet')
            ->table('users')
            ->where('email', $email)
            ->first();

        $token = bin2hex(random_bytes(32));
        $expiresAt = Carbon::now()->addHours(24);

        if ($existing) {
            $sameOrg = (int) ($existing->organisation_id ?? 0) === $organisationId;
            $isPending = ($existing->account_status ?? null) === 'pending';
            $isDriverRole = ($existing->role ?? null) === 'driver';

            if ($sameOrg && $isPending && $isDriverRole) {
                DB::connection('sharpfleet')
                    ->table('users')
                    ->where('id', $existing->id)
                    ->update([
                        'activation_token' => $token,
                        'activation_expires_at' => $expiresAt,
                        'updated_at' => Carbon::now(),
                    ]);

                Mail::to($email)->send(new DriverInvitation((object) [
                    'email' => $email,
                    'organisation_name' => $organisation->name,
                    'activation_token' => $token,
                ]));

                return redirect('/app/sharpfleet/admin/users')
                    ->with('success', 'Invitation re-sent.');
            }

            // Any other state: treat as not supported (brand-new only).
            return back()->withErrors([
                'email' => 'That email is already registered. For safety, SharpFleet invites are for brand-new users only.'
            ])->withInput();
        }

        DB::connection('sharpfleet')
            ->table('users')
            ->insert([
                // Some deployments enforce NOT NULL on these columns.
                // Drivers will set their real names when accepting the invite.
                'first_name' => '',
                'last_name' => '',
                'email' => $email,
                'organisation_id' => $organisationId,
                'role' => 'driver',
                'is_driver' => 1,
                'account_status' => 'pending',
                'activation_token' => $token,
                'activation_expires_at' => $expiresAt,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

        Mail::to($email)->send(new DriverInvitation((object) [
            'email' => $email,
            'organisation_name' => $organisation->name,
            'activation_token' => $token,
        ]));

        return redirect('/app/sharpfleet/admin/users')
            ->with('success', 'Driver invitation sent.');
    }

    public function resend(Request $request, int $userId)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $user = DB::connection('sharpfleet')
            ->table('users')
            ->where('organisation_id', $organisationId)
            ->where('id', $userId)
            ->first();

        if (!$user) {
            abort(404);
        }

        if (($user->account_status ?? null) !== 'pending' || ($user->role ?? null) !== 'driver') {
            return redirect('/app/sharpfleet/admin/users')
                ->withErrors(['error' => 'Only pending driver invites can be re-sent.']);
        }

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->select('id', 'name')
            ->where('id', $organisationId)
            ->first();

        $token = bin2hex(random_bytes(32));
        $expiresAt = Carbon::now()->addHours(24);

        DB::connection('sharpfleet')
            ->table('users')
            ->where('id', $userId)
            ->update([
                'activation_token' => $token,
                'activation_expires_at' => $expiresAt,
                'updated_at' => Carbon::now(),
            ]);

        Mail::to($user->email)->send(new DriverInvitation((object) [
            'email' => $user->email,
            'organisation_name' => $organisation->name ?? 'your organisation',
            'activation_token' => $token,
        ]));

        return redirect('/app/sharpfleet/admin/users')
            ->with('success', 'Invitation re-sent.');
    }
}
