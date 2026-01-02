<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SharpFleet\DriverInvitation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

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

    public function createManual(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->select('id', 'name')
            ->where('id', $organisationId)
            ->first();

        return view('sharpfleet.admin.users.add', [
            'organisation' => $organisation,
        ]);
    }

    public function storeManual(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));
        $firstName = trim((string) ($validated['first_name'] ?? ''));
        $lastName = trim((string) ($validated['last_name'] ?? ''));

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->select('id', 'name')
            ->where('id', $organisationId)
            ->first();

        if (!$organisation) {
            abort(403, 'Organisation not found.');
        }

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
                $updates = [
                    'activation_token' => $token,
                    'activation_expires_at' => $expiresAt,
                    'updated_at' => Carbon::now(),
                ];

                // If the admin preloaded a name, store it (non-breaking).
                if ($firstName !== '' && (string) ($existing->first_name ?? '') === '') {
                    $updates['first_name'] = $firstName;
                }
                if ($lastName !== '' && (string) ($existing->last_name ?? '') === '') {
                    $updates['last_name'] = $lastName;
                }

                DB::connection('sharpfleet')
                    ->table('users')
                    ->where('id', $existing->id)
                    ->update($updates);

                Mail::to($email)->send(new DriverInvitation((object) [
                    'email' => $email,
                    'organisation_name' => $organisation->name,
                    'activation_token' => $token,
                ]));

                return redirect('/app/sharpfleet/admin/users')
                    ->with('success', 'Invitation re-sent.');
            }

            return back()->withErrors([
                'email' => 'That email is already registered. For safety, driver invites are for brand-new users only.'
            ])->withInput();
        }

        DB::connection('sharpfleet')
            ->table('users')
            ->insert([
                'first_name' => $firstName,
                'last_name' => $lastName,
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
            ->with('success', 'Driver added and invitation sent.');
    }

    public function createImport(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->select('id', 'name')
            ->where('id', $organisationId)
            ->first();

        return view('sharpfleet.admin.users.import', [
            'organisation' => $organisation,
        ]);
    }

    public function storeImport(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->select('id', 'name')
            ->where('id', $organisationId)
            ->first();

        if (!$organisation) {
            abort(403, 'Organisation not found.');
        }

        $file = $request->file('csv');
        $path = $file ? $file->getRealPath() : null;

        if (!$path) {
            return back()->withErrors(['csv' => 'Unable to read uploaded CSV.'])->withInput();
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            return back()->withErrors(['csv' => 'Unable to open uploaded CSV.'])->withInput();
        }

        $created = 0;
        $resent = 0;
        $skipped = 0;
        $invalid = 0;

        $header = null;

        while (($row = fgetcsv($handle)) !== false) {
            if (!is_array($row)) {
                continue;
            }

            // Skip empty rows
            $nonEmpty = array_filter($row, fn ($v) => trim((string) $v) !== '');
            if (count($nonEmpty) === 0) {
                continue;
            }

            if ($header === null) {
                $maybeHeader = array_map(fn ($v) => strtolower(trim((string) $v)), $row);
                if (in_array('email', $maybeHeader, true)) {
                    $header = $maybeHeader;
                    continue;
                }
                $header = []; // No header; treat this row as data
            }

            $email = '';
            $firstName = '';
            $lastName = '';

            if ($header) {
                $map = [];
                foreach ($header as $idx => $key) {
                    $map[$key] = $idx;
                }
                $email = isset($map['email']) ? (string) ($row[$map['email']] ?? '') : '';
                $firstName = isset($map['first_name']) ? (string) ($row[$map['first_name']] ?? '') : '';
                $lastName = isset($map['last_name']) ? (string) ($row[$map['last_name']] ?? '') : '';
            } else {
                // No header: assume email, first_name, last_name
                $email = (string) ($row[0] ?? '');
                $firstName = (string) ($row[1] ?? '');
                $lastName = (string) ($row[2] ?? '');
            }

            $email = strtolower(trim($email));
            $firstName = trim($firstName);
            $lastName = trim($lastName);

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $invalid++;
                continue;
            }

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
                    $updates = [
                        'activation_token' => $token,
                        'activation_expires_at' => $expiresAt,
                        'updated_at' => Carbon::now(),
                    ];

                    if ($firstName !== '' && (string) ($existing->first_name ?? '') === '') {
                        $updates['first_name'] = $firstName;
                    }
                    if ($lastName !== '' && (string) ($existing->last_name ?? '') === '') {
                        $updates['last_name'] = $lastName;
                    }

                    DB::connection('sharpfleet')
                        ->table('users')
                        ->where('id', $existing->id)
                        ->update($updates);

                    Mail::to($email)->send(new DriverInvitation((object) [
                        'email' => $email,
                        'organisation_name' => $organisation->name,
                        'activation_token' => $token,
                    ]));

                    $resent++;
                    continue;
                }

                $skipped++;
                continue;
            }

            DB::connection('sharpfleet')
                ->table('users')
                ->insert([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
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

            $created++;
        }

        fclose($handle);

        $message = sprintf(
            'Import complete: %d created, %d re-sent, %d skipped, %d invalid.',
            $created,
            $resent,
            $skipped,
            $invalid
        );

        return redirect('/app/sharpfleet/admin/users')
            ->with('success', $message);
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
