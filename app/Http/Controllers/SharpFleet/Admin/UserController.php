<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $users = DB::connection('sharpfleet')
            ->table('users')
            ->select('id', 'first_name', 'last_name', 'email', 'role', 'is_driver', 'account_status', 'activation_expires_at')
            ->where('organisation_id', $organisationId)
            ->where(function ($q) {
                $q->whereNull('account_status')
                    ->orWhere('account_status', '!=', 'deleted');
            })
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('sharpfleet.admin.users.index', [
            'users' => $users,
        ]);
    }

    public function edit(Request $request, int $userId)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $user = DB::connection('sharpfleet')
            ->table('users')
            ->select('id', 'first_name', 'last_name', 'email', 'role', 'is_driver')
            ->where('organisation_id', $organisationId)
            ->where('id', $userId)
            ->where(function ($q) {
                $q->whereNull('account_status')
                    ->orWhere('account_status', '!=', 'deleted');
            })
            ->first();

        if (!$user) {
            abort(404);
        }

        return view('sharpfleet.admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, int $userId)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $request->validate([
            'is_driver' => ['required', 'in:0,1'],
        ]);

        // The form submits a hidden 0 plus a checkbox 1 when checked.
        // Read the resulting scalar value deterministically.
        $isDriver = ((int) $request->input('is_driver', 0) === 1) ? 1 : 0;

        $updated = DB::connection('sharpfleet')
            ->table('users')
            ->where('organisation_id', $organisationId)
            ->where('id', $userId)
            ->update([
                'is_driver' => $isDriver,
                'updated_at' => now(),
            ]);

        if ($updated === 0) {
            abort(404);
        }

        // If the admin edited their own driver access, update the session so it takes effect immediately.
        if ((int) ($fleetUser['id'] ?? 0) === (int) $userId) {
            $request->session()->put('sharpfleet.user.is_driver', $isDriver);
        }

        return redirect('/app/sharpfleet/admin/users')
            ->with('success', 'User updated.');
    }

    public function destroy(Request $request, int $userId)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);
        $currentUserId = (int) ($fleetUser['id'] ?? 0);

        if ($currentUserId === $userId) {
            return redirect('/app/sharpfleet/admin/users')
                ->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $user = DB::connection('sharpfleet')
            ->table('users')
            ->select('id', 'email', 'role', 'account_status')
            ->where('organisation_id', $organisationId)
            ->where('id', $userId)
            ->first();

        if (!$user) {
            abort(404);
        }

        // Only allow deleting drivers via this screen.
        if (($user->role ?? null) !== 'driver') {
            return redirect('/app/sharpfleet/admin/users')
                ->withErrors(['error' => 'Only driver accounts can be deleted.']);
        }

        if (($user->account_status ?? null) === 'deleted') {
            return redirect('/app/sharpfleet/admin/users')
                ->with('success', 'Driver already deleted.');
        }

        // Soft-delete (keeps historical references intact) but prevents future logins.
        // Also change email to avoid unique constraint collisions.
        $deletedEmail = 'deleted+' . $userId . '+' . now()->timestamp . '@example.invalid';

        $updates = [
            'email' => $deletedEmail,
            'account_status' => 'deleted',
            'is_driver' => 0,
            'updated_at' => now(),
        ];

        // Different SharpFleet deployments have slightly different schemas.
        // Only clear auth/activation fields if the columns exist.
        if (Schema::connection('sharpfleet')->hasColumn('users', 'password_hash')) {
            $updates['password_hash'] = null;
        }
        if (Schema::connection('sharpfleet')->hasColumn('users', 'remember_token')) {
            $updates['remember_token'] = null;
        }
        if (Schema::connection('sharpfleet')->hasColumn('users', 'activation_token')) {
            $updates['activation_token'] = null;
        }
        if (Schema::connection('sharpfleet')->hasColumn('users', 'activation_expires_at')) {
            $updates['activation_expires_at'] = null;
        }

        DB::connection('sharpfleet')
            ->table('users')
            ->where('organisation_id', $organisationId)
            ->where('id', $userId)
            ->update($updates);

        return redirect('/app/sharpfleet/admin/users')
            ->with('success', 'Driver deleted.');
    }
}
