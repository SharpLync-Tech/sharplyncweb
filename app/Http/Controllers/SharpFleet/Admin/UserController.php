<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $users = DB::connection('sharpfleet')
            ->table('users')
            ->select('id', 'first_name', 'last_name', 'email', 'role', 'is_driver')
            ->where('organisation_id', $organisationId)
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
}
