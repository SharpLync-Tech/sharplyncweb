<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SharpFleet\DriverInvitation;
use App\Services\SharpFleet\BranchService;
use App\Support\SharpFleet\OrganisationAccount;
use App\Support\SharpFleet\Roles;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class DriverInviteController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            $user = $request->session()->get('sharpfleet.user');
            if (!$user) {
                abort(403);
            }

            $organisationId = (int) ($user['organisation_id'] ?? 0);
            if (!OrganisationAccount::usersEnabled($organisationId)) {
                abort(403, 'User management is not available for this account type.');
            }

            return $next($request);
        });
    }

    private function resolveActorBranchIds(array $fleetUser, int $organisationId, BranchService $branchService): array
    {
        if (Roles::bypassesBranchRestrictions($fleetUser)) {
            return [];
        }

        $branchesEnabled = $branchService->branchesEnabled() && $branchService->userBranchAccessEnabled();
        if (!$branchesEnabled) {
            return [];
        }

        $actorId = (int) ($fleetUser['id'] ?? 0);
        $ids = $branchService->getAccessibleBranchIdsForUser($organisationId, $actorId);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn ($v) => $v > 0)));

        if (count($ids) > 0) {
            return $ids;
        }

        $defaultBranchId = (int) ($branchService->ensureDefaultBranch($organisationId) ?? 0);
        return $defaultBranchId > 0 ? [$defaultBranchId] : [];
    }

    private function assertActorCanAccessUser(array $fleetUser, int $organisationId, int $targetUserId, BranchService $branchService): void
    {
        if (Roles::bypassesBranchRestrictions($fleetUser)) {
            return;
        }

        $branchesEnabled = $branchService->branchesEnabled() && $branchService->userBranchAccessEnabled();
        if (!$branchesEnabled) {
            return;
        }

        $actorBranchIds = $this->resolveActorBranchIds($fleetUser, $organisationId, $branchService);
        if (count($actorBranchIds) === 0) {
            abort(403, 'You do not have access to any branches.');
        }

        $targetBranchIds = $branchService->getAccessibleBranchIdsForUser($organisationId, $targetUserId);
        $targetBranchIds = array_values(array_unique(array_filter(array_map('intval', $targetBranchIds), fn ($v) => $v > 0)));

        if (count(array_intersect($actorBranchIds, $targetBranchIds)) === 0) {
            abort(403, 'You can only manage users in your branch.');
        }
    }

    private function assignUserToActorBranches(int $organisationId, int $userId, array $fleetUser): void
    {
        $branchService = new BranchService();
        $branchesEnabled = $branchService->branchesEnabled() && $branchService->userBranchAccessEnabled();
        if (!$branchesEnabled) {
            return;
        }

        if (Roles::bypassesBranchRestrictions($fleetUser)) {
            return;
        }

        $actorBranchIds = $this->resolveActorBranchIds($fleetUser, $organisationId, $branchService);
        if (count($actorBranchIds) === 0) {
            return;
        }

        $hasIsActive = Schema::connection('sharpfleet')->hasColumn('user_branch_access', 'is_active');
        $hasCreatedAt = Schema::connection('sharpfleet')->hasColumn('user_branch_access', 'created_at');
        $hasUpdatedAt = Schema::connection('sharpfleet')->hasColumn('user_branch_access', 'updated_at');

        DB::connection('sharpfleet')->transaction(function () use ($organisationId, $userId, $actorBranchIds, $hasIsActive, $hasCreatedAt, $hasUpdatedAt) {
            foreach ($actorBranchIds as $branchId) {
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

                DB::connection('sharpfleet')->table('user_branch_access')->updateOrInsert($attrs, $values);

                if ($hasUpdatedAt) {
                    DB::connection('sharpfleet')
                        ->table('user_branch_access')
                        ->where($attrs)
                        ->update(['updated_at' => now()] + ($hasIsActive ? ['is_active' => 1] : []));
                }
            }
        });
    }

    public function create(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        if (!$fleetUser || !Roles::canManageUsers($fleetUser)) {
            abort(403);
        }
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
        if (!$fleetUser || !Roles::canManageUsers($fleetUser)) {
            abort(403);
        }
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
                // Ensure branch admins can only resend within their branch.
                $branchService = new BranchService();
                $this->assertActorCanAccessUser($fleetUser, $organisationId, (int) $existing->id, $branchService);

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

        $newUserId = (int) DB::connection('sharpfleet')->table('users')->where('organisation_id', $organisationId)->where('email', $email)->value('id');
        if ($newUserId > 0) {
            $this->assignUserToActorBranches($organisationId, $newUserId, $fleetUser);
        }

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
        if (!$fleetUser || !Roles::canManageUsers($fleetUser)) {
            abort(403);
        }
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
        if (!$fleetUser || !Roles::canManageUsers($fleetUser)) {
            abort(403);
        }
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

        if ($existing) {
            $sameOrg = (int) ($existing->organisation_id ?? 0) === $organisationId;
            $isPending = ($existing->account_status ?? null) === 'pending';
            $isDriverRole = ($existing->role ?? null) === 'driver';

            if ($sameOrg && $isPending && $isDriverRole) {
                $branchService = new BranchService();
                $this->assertActorCanAccessUser($fleetUser, $organisationId, (int) $existing->id, $branchService);

                $updates = [
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

                return redirect('/app/sharpfleet/admin/users')
                    ->with('success', 'Driver added.');
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
                'activation_token' => null,
                'activation_expires_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

        $newUserId = (int) DB::connection('sharpfleet')->table('users')->where('organisation_id', $organisationId)->where('email', $email)->value('id');
        if ($newUserId > 0) {
            $this->assignUserToActorBranches($organisationId, $newUserId, $fleetUser);
        }

        return redirect('/app/sharpfleet/admin/users')
            ->with('success', 'Driver added.');
    }

    public function createImport(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        if (!$fleetUser || !Roles::canManageUsers($fleetUser)) {
            abort(403);
        }
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
        if (!$fleetUser || !Roles::canManageUsers($fleetUser)) {
            abort(403);
        }
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
        $updated = 0;
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

            if ($existing) {
                $sameOrg = (int) ($existing->organisation_id ?? 0) === $organisationId;
                $isPending = ($existing->account_status ?? null) === 'pending';
                $isDriverRole = ($existing->role ?? null) === 'driver';

                if ($sameOrg && $isPending && $isDriverRole) {
                    $branchService = new BranchService();
                    $this->assertActorCanAccessUser($fleetUser, $organisationId, (int) $existing->id, $branchService);

                    $updates = [
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

                    $updated++;
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
                    'activation_token' => null,
                    'activation_expires_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            $newUserId = (int) DB::connection('sharpfleet')->table('users')->where('organisation_id', $organisationId)->where('email', $email)->value('id');
            if ($newUserId > 0) {
                $this->assignUserToActorBranches($organisationId, $newUserId, $fleetUser);
            }
            $created++;
        }

        fclose($handle);

        $message = sprintf(
            'Import complete: %d added, %d updated, %d skipped, %d invalid.',
            $created,
            $updated,
            $skipped,
            $invalid
        );

        return redirect('/app/sharpfleet/admin/users')
            ->with('success', $message);
    }

    public function sendInvites(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        if (!$fleetUser || !Roles::canManageUsers($fleetUser)) {
            abort(403);
        }
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer'],
        ]);

        $organisation = DB::connection('sharpfleet')
            ->table('organisations')
            ->select('id', 'name')
            ->where('id', $organisationId)
            ->first();

        if (!$organisation) {
            abort(403, 'Organisation not found.');
        }

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($validated['user_ids'] as $userId) {
            $userId = (int) $userId;

            $user = DB::connection('sharpfleet')
                ->table('users')
                ->where('organisation_id', $organisationId)
                ->where('id', $userId)
                ->first();

            if (!$user) {
                $skipped++;
                continue;
            }

            $branchService = new BranchService();
            $this->assertActorCanAccessUser($fleetUser, $organisationId, $userId, $branchService);

            if (($user->role ?? null) !== 'driver' || ($user->account_status ?? null) !== 'pending') {
                $skipped++;
                continue;
            }

            $email = strtolower(trim((string) ($user->email ?? '')));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

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

            try {
                Mail::to($email)->send(new DriverInvitation((object) [
                    'email' => $email,
                    'organisation_name' => $organisation->name,
                    'activation_token' => $token,
                ]));
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        return redirect('/app/sharpfleet/admin/users')
            ->with('success', sprintf('Invites sent: %d. Skipped: %d. Failed: %d.', $sent, $skipped, $failed));
    }

    public function resend(Request $request, int $userId)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        if (!$fleetUser || !Roles::canManageUsers($fleetUser)) {
            abort(403);
        }
        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $user = DB::connection('sharpfleet')
            ->table('users')
            ->where('organisation_id', $organisationId)
            ->where('id', $userId)
            ->first();

        if (!$user) {
            abort(404);
        }

        $branchService = new BranchService();
        $this->assertActorCanAccessUser($fleetUser, $organisationId, $userId, $branchService);

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
