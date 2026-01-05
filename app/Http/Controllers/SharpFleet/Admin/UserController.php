<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BranchService;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || !Roles::canManageUsers($fleetUser)) {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $status = strtolower(trim((string) $request->query('status', 'active')));
        if (!in_array($status, ['active', 'archived', 'all'], true)) {
            $status = 'active';
        }

        $hasArchivedAt = Schema::connection('sharpfleet')->hasColumn('users', 'archived_at');

        $select = ['id', 'first_name', 'last_name', 'email', 'role', 'is_driver', 'account_status', 'activation_expires_at'];
        if ($hasArchivedAt) {
            $select[] = 'archived_at';
        }

        $query = DB::connection('sharpfleet')
            ->table('users')
            ->select($select)
            ->where('organisation_id', $organisationId)
            ->where('email', 'not like', 'deleted+%@example.invalid')
            ->where(function ($q) {
                $q->whereNull('account_status')
                    ->orWhere('account_status', '!=', 'deleted');
            })

            // Archive filter (schema-guarded for backwards compatibility)
            ->when($hasArchivedAt && $status !== 'all', function ($q) use ($status) {
                if ($status === 'archived') {
                    return $q->whereNotNull('archived_at');
                }

                return $q->whereNull('archived_at');
            });

        if ($hasArchivedAt && $status === 'archived') {
            $query->orderByDesc('archived_at');
        }

        $users = $query
            ->orderByRaw("CASE WHEN role IN ('company_admin','admin') THEN 0 ELSE 1 END")
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('sharpfleet.admin.users.index', [
            'users' => $users,
            'status' => $status,
        ]);
    }

    public function edit(Request $request, int $userId)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || !Roles::canManageUsers($fleetUser)) {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $hasArchivedAt = Schema::connection('sharpfleet')->hasColumn('users', 'archived_at');

        $select = ['id', 'first_name', 'last_name', 'email', 'role', 'is_driver'];
        if ($hasArchivedAt) {
            $select[] = 'archived_at';
        }

        $user = DB::connection('sharpfleet')
            ->table('users')
            ->select($select)
            ->where('organisation_id', $organisationId)
            ->where('id', $userId)
            ->where('email', 'not like', 'deleted+%@example.invalid')
            ->where(function ($q) {
                $q->whereNull('account_status')
                    ->orWhere('account_status', '!=', 'deleted');
            })
            ->first();

        if (!$user) {
            abort(404);
        }

        $branchService = new BranchService();
        $branchesEnabled = $branchService->branchesEnabled() && $branchService->userBranchAccessEnabled();
        $branches = $branchesEnabled ? $branchService->getBranches($organisationId) : collect();
        $selectedBranchIds = $branchesEnabled ? $branchService->getAccessibleBranchIdsForUser($organisationId, $userId) : [];

        return view('sharpfleet.admin.users.edit', [
            'user' => $user,
            'branchesEnabled' => $branchesEnabled,
            'branches' => $branches,
            'selectedBranchIds' => $selectedBranchIds,
        ]);
    }

    public function update(Request $request, int $userId)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || !Roles::canManageUsers($fleetUser)) {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $request->validate([
            'is_driver' => ['required', 'in:0,1'],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['integer'],
        ]);

        // The form submits a hidden 0 plus a checkbox 1 when checked.
        // Read the resulting scalar value deterministically.
        $isDriver = ((int) $request->input('is_driver', 0) === 1) ? 1 : 0;

        $branchService = new BranchService();
        $branchesEnabled = $branchService->branchesEnabled() && $branchService->userBranchAccessEnabled();

        // Persist branch access (schema-guarded). Defaults to the default branch if none provided.
        if ($branchesEnabled) {
            $incoming = $request->input('branch_ids', []);
            $incoming = is_array($incoming) ? $incoming : [];

            $selected = [];
            foreach ($incoming as $bid) {
                $bid = (int) $bid;
                if ($bid > 0 && $branchService->getBranch($organisationId, $bid)) {
                    $selected[$bid] = true;
                }
            }

            if (count($selected) === 0) {
                $defaultBranchId = (int) ($branchService->ensureDefaultBranch($organisationId) ?? 0);
                if ($defaultBranchId > 0) {
                    $selected[$defaultBranchId] = true;
                }
            }

            $hasIsActive = Schema::connection('sharpfleet')->hasColumn('user_branch_access', 'is_active');
            $hasCreatedAt = Schema::connection('sharpfleet')->hasColumn('user_branch_access', 'created_at');
            $hasUpdatedAt = Schema::connection('sharpfleet')->hasColumn('user_branch_access', 'updated_at');

            DB::connection('sharpfleet')->transaction(function () use ($organisationId, $userId, $selected, $hasIsActive, $hasCreatedAt, $hasUpdatedAt) {
                if ($hasIsActive) {
                    DB::connection('sharpfleet')
                        ->table('user_branch_access')
                        ->where('organisation_id', $organisationId)
                        ->where('user_id', $userId)
                        ->update(['is_active' => 0]);
                }

                foreach (array_keys($selected) as $branchId) {
                    $attrs = [
                        'organisation_id' => $organisationId,
                        'user_id' => $userId,
                        'branch_id' => (int) $branchId,
                    ];

                    $values = [];
                    if ($hasIsActive) {
                        $values['is_active'] = 1;
                    }
                    if ($hasCreatedAt) {
                        $values['created_at'] = now();
                    }
                    if ($hasUpdatedAt) {
                        $values['updated_at'] = now();
                    }

                    // updateOrInsert will update existing rows or create new ones.
                    DB::connection('sharpfleet')->table('user_branch_access')->updateOrInsert($attrs, $values);

                    // Ensure updated_at is refreshed for existing rows.
                    if ($hasUpdatedAt) {
                        DB::connection('sharpfleet')
                            ->table('user_branch_access')
                            ->where($attrs)
                            ->update(['updated_at' => now()] + ($hasIsActive ? ['is_active' => 1] : []));
                    }
                }
            });
        }

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

        if (!$fleetUser || !Roles::canManageUsers($fleetUser)) {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);
        $currentUserId = (int) ($fleetUser['id'] ?? 0);

        if ($currentUserId === $userId) {
            return redirect('/app/sharpfleet/admin/users')
                ->withErrors(['error' => 'You cannot archive your own account.']);
        }

        $user = DB::connection('sharpfleet')
            ->table('users')
            ->select('id', 'email', 'role', 'account_status', 'archived_at')
            ->where('organisation_id', $organisationId)
            ->where('id', $userId)
            ->first();

        if (!$user) {
            abort(404);
        }

        // Only allow deleting drivers via this screen.
        if (($user->role ?? null) !== 'driver') {
            return redirect('/app/sharpfleet/admin/users')
                ->withErrors(['error' => 'Only driver accounts can be archived.']);
        }

        if (Schema::connection('sharpfleet')->hasColumn('users', 'archived_at') && !empty($user->archived_at)) {
            return redirect('/app/sharpfleet/admin/users')
                ->with('success', 'Driver already archived.');
        }

        $updates = [
            'is_driver' => 0,
            'updated_at' => now(),
        ];

        if (Schema::connection('sharpfleet')->hasColumn('users', 'archived_at')) {
            $updates['archived_at'] = now();
        }

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
            ->with('success', 'Driver archived.');
    }

    public function unarchive(Request $request, int $userId)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || !Roles::canManageUsers($fleetUser)) {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        if (!Schema::connection('sharpfleet')->hasColumn('users', 'archived_at')) {
            return redirect('/app/sharpfleet/admin/users')
                ->withErrors(['error' => 'Archiving is not enabled for this account yet.']);
        }

        $user = DB::connection('sharpfleet')
            ->table('users')
            ->select('id', 'role', 'archived_at')
            ->where('organisation_id', $organisationId)
            ->where('id', $userId)
            ->first();

        if (!$user) {
            abort(404);
        }

        // Keep behaviour consistent with archive: only manage driver accounts here.
        if (($user->role ?? null) !== 'driver') {
            return redirect('/app/sharpfleet/admin/users?status=archived')
                ->withErrors(['error' => 'Only driver accounts can be re-enabled here.']);
        }

        if (empty($user->archived_at)) {
            return redirect('/app/sharpfleet/admin/users')
                ->with('success', 'User is already active.');
        }

        DB::connection('sharpfleet')
            ->table('users')
            ->where('organisation_id', $organisationId)
            ->where('id', $userId)
            ->update([
                'archived_at' => null,
                'updated_at' => now(),
            ]);

        return redirect('/app/sharpfleet/admin/users?status=archived')
            ->with('success', 'User re-enabled successfully.');
    }
}
