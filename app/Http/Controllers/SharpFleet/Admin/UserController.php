<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BranchService;
use App\Services\SharpFleet\MobileTokenService;
use App\Services\SharpFleet\AuditLogService;
use App\Services\SharpFleet\CompanySettingsService;
use App\Support\SharpFleet\Roles;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    private function resolveActorBranchIds(array $fleetUser, int $organisationId, BranchService $branchService): array
    {
        if (Roles::bypassesBranchRestrictions($fleetUser)) {
            return [];
        }

        $branchesEnabled = $branchService->branchesEnabled();
        $branchAccessEnabled = $branchesEnabled && $branchService->userBranchAccessEnabled();
        if (!$branchesEnabled) {
            return [];
        }

        $actorId = (int) ($fleetUser['id'] ?? 0);
        $ids = [];
        if ($branchAccessEnabled) {
            $ids = $branchService->getAccessibleBranchIdsForUser($organisationId, $actorId);
            $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn ($v) => $v > 0)));
        } else {
            $primary = $branchService->getPrimaryBranchIdForUser($organisationId, $actorId);
            if ($primary) {
                $ids = [(int) $primary];
            }
        }

        if (count($ids) > 0) {
            return $ids;
        }

        $defaultBranchId = (int) ($branchService->ensureDefaultBranch($organisationId) ?? 0);
        return $defaultBranchId > 0 ? [$defaultBranchId] : [];
    }

    private function assertActorCanManageTargetUser(array $fleetUser, int $organisationId, int $targetUserId, BranchService $branchService): void
    {
        if (Roles::bypassesBranchRestrictions($fleetUser)) {
            return;
        }

        $branchesEnabled = $branchService->branchesEnabled();
        $branchAccessEnabled = $branchesEnabled && $branchService->userBranchAccessEnabled();
        if (!$branchesEnabled) {
            return;
        }

        $actorBranchIds = $this->resolveActorBranchIds($fleetUser, $organisationId, $branchService);
        if (count($actorBranchIds) === 0) {
            abort(403, 'You do not have access to any branches.');
        }

        if ($branchAccessEnabled) {
            $targetBranchIds = $branchService->getAccessibleBranchIdsForUser($organisationId, $targetUserId);
            $targetBranchIds = array_values(array_unique(array_filter(array_map('intval', $targetBranchIds), fn ($v) => $v > 0)));

            $hasIntersection = count(array_intersect($actorBranchIds, $targetBranchIds)) > 0;
            if (!$hasIntersection) {
                abort(403, 'You can only manage users in your branch.');
            }
        } else {
            if (Schema::connection('sharpfleet')->hasColumn('users', 'branch_id')) {
                $targetBranchId = (int) (DB::connection('sharpfleet')
                    ->table('users')
                    ->where('organisation_id', $organisationId)
                    ->where('id', $targetUserId)
                    ->value('branch_id') ?? 0);

                if ($targetBranchId > 0 && !in_array($targetBranchId, $actorBranchIds, true)) {
                    abort(403, 'You can only manage users in your branch.');
                }
            }
        }
    }

    public function index(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || !Roles::canSetUserGroups($fleetUser)) {
            abort(403);
        }

        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);
        $actorRole = Roles::normalize($fleetUser['role'] ?? null);

        $branchService = new BranchService();
        $actorBranchIds = $this->resolveActorBranchIds($fleetUser, $organisationId, $branchService);
        $branchAccessEnabled = $branchService->branchesEnabled() && $branchService->userBranchAccessEnabled();
        $hasUserBranchId = Schema::connection('sharpfleet')->hasColumn('users', 'branch_id');

        $status = strtolower(trim((string) $request->query('status', 'active')));
        if (!in_array($status, ['active', 'archived', 'all'], true)) {
            $status = 'active';
        }

        $search = trim((string) $request->query('search', ''));

        $hasArchivedAt = Schema::connection('sharpfleet')->hasColumn('users', 'archived_at');

        // Qualify columns because branch admins may join user_branch_access, which can introduce ambiguity.
        $select = ['users.id as id', 'users.first_name', 'users.last_name', 'users.email', 'users.role', 'users.is_driver', 'users.account_status', 'users.activation_expires_at'];
        if ($hasArchivedAt) {
            $select[] = 'users.archived_at';
        }

        $query = DB::connection('sharpfleet')
            ->table('users')
            ->select($select)
            ->where('users.organisation_id', $organisationId)
            ->where('users.email', 'not like', 'deleted+%@example.invalid')
            ->when($actorRole !== Roles::COMPANY_ADMIN, function ($q) {
                $q->whereNotIn('users.role', [Roles::COMPANY_ADMIN, 'admin']);
            })
            ->where(function ($q) {
                $q->whereNull('users.account_status')
                    ->orWhere('users.account_status', '!=', 'deleted');
            })

            // Branch restriction (only for non-company admins; schema-guarded)
            ->when(count($actorBranchIds) > 0 && $branchAccessEnabled, function ($q) use ($organisationId, $actorBranchIds) {
                $q->join('user_branch_access as uba', function ($join) use ($organisationId) {
                    $join->on('users.id', '=', 'uba.user_id')
                        ->where('uba.organisation_id', '=', $organisationId);
                });

                if (Schema::connection('sharpfleet')->hasColumn('user_branch_access', 'is_active')) {
                    $q->where('uba.is_active', 1);
                }

                $q->whereIn('uba.branch_id', $actorBranchIds);
                $q->distinct();
            })
            ->when(!$branchAccessEnabled && $hasUserBranchId && count($actorBranchIds) > 0, function ($q) use ($actorBranchIds) {
                $q->whereIn('users.branch_id', $actorBranchIds);
            })

            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where(function ($q) use ($like) {
                    $q->where('users.first_name', 'like', $like)
                        ->orWhere('users.last_name', 'like', $like)
                        ->orWhere('users.email', 'like', $like);
                });
            })

            // Archive filter (schema-guarded for backwards compatibility)
            ->when($hasArchivedAt && $status !== 'all', function ($q) use ($status) {
                if ($status === 'archived') {
                    return $q->whereNotNull('users.archived_at');
                }

                return $q->whereNull('users.archived_at');
            });

        if ($hasArchivedAt && $status === 'archived') {
            $query->orderByDesc('users.archived_at');
        }

        $users = $query
            ->orderByRaw("CASE WHEN users.role IN ('company_admin','admin') THEN 0 ELSE 1 END")
            ->orderBy('users.first_name')
            ->orderBy('users.last_name')
            ->get();

        return view('sharpfleet.admin.users.index', [
            'users' => $users,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function search(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || !Roles::canSetUserGroups($fleetUser)) {
            abort(403);
        }

        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);
        $actorRole = Roles::normalize($fleetUser['role'] ?? null);

        $branchService = new BranchService();
        $actorBranchIds = $this->resolveActorBranchIds($fleetUser, $organisationId, $branchService);
        $branchAccessEnabled = $branchService->branchesEnabled() && $branchService->userBranchAccessEnabled();
        $hasUserBranchId = Schema::connection('sharpfleet')->hasColumn('users', 'branch_id');

        $status = strtolower(trim((string) $request->query('status', 'active')));
        if (!in_array($status, ['active', 'archived', 'all'], true)) {
            $status = 'active';
        }

        $search = trim((string) $request->query('query', ''));
        if ($search === '') {
            return response()->json([]);
        }

        $hasArchivedAt = Schema::connection('sharpfleet')->hasColumn('users', 'archived_at');

        $select = ['users.id as id', 'users.first_name', 'users.last_name', 'users.email', 'users.role'];
        if ($hasArchivedAt) {
            $select[] = 'users.archived_at';
        }

        $query = DB::connection('sharpfleet')
            ->table('users')
            ->select($select)
            ->where('users.organisation_id', $organisationId)
            ->where('users.email', 'not like', 'deleted+%@example.invalid')
            ->when($actorRole !== Roles::COMPANY_ADMIN, function ($q) {
                $q->whereNotIn('users.role', [Roles::COMPANY_ADMIN, 'admin']);
            })
            ->where(function ($q) {
                $q->whereNull('users.account_status')
                    ->orWhere('users.account_status', '!=', 'deleted');
            })
            ->when(count($actorBranchIds) > 0 && $branchAccessEnabled, function ($q) use ($organisationId, $actorBranchIds) {
                $q->join('user_branch_access as uba', function ($join) use ($organisationId) {
                    $join->on('users.id', '=', 'uba.user_id')
                        ->where('uba.organisation_id', '=', $organisationId);
                });

                if (Schema::connection('sharpfleet')->hasColumn('user_branch_access', 'is_active')) {
                    $q->where('uba.is_active', 1);
                }

                $q->whereIn('uba.branch_id', $actorBranchIds);
                $q->distinct();
            })
            ->when(!$branchAccessEnabled && $hasUserBranchId && count($actorBranchIds) > 0, function ($q) use ($actorBranchIds) {
                $q->whereIn('users.branch_id', $actorBranchIds);
            })
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where(function ($q) use ($like) {
                    $q->where('users.first_name', 'like', $like)
                        ->orWhere('users.last_name', 'like', $like)
                        ->orWhere('users.email', 'like', $like);
                });
            })
            ->when($hasArchivedAt && $status !== 'all', function ($q) use ($status) {
                if ($status === 'archived') {
                    return $q->whereNotNull('users.archived_at');
                }

                return $q->whereNull('users.archived_at');
            });

        $users = $query
            ->orderByRaw("CASE WHEN users.role IN ('company_admin','admin') THEN 0 ELSE 1 END")
            ->orderBy('users.first_name')
            ->orderBy('users.last_name')
            ->limit(8)
            ->get()
            ->map(function ($user) {
                $first = trim((string) ($user->first_name ?? ''));
                $last = trim((string) ($user->last_name ?? ''));
                $name = trim($first . ' ' . $last);

                return [
                    'id' => $user->id,
                    'name' => $name !== '' ? $name : ($user->email ?? ''),
                    'email' => $user->email ?? '',
                    'role' => $user->role ?? '',
                ];
            })
            ->values();

        return response()->json($users);
    }

    public function edit(Request $request, int $userId)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || !Roles::canSetUserGroups($fleetUser)) {
            abort(403);
        }

        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);
        $actorRole = Roles::normalize($fleetUser['role'] ?? null);

        $branchService = new BranchService();
        $this->assertActorCanManageTargetUser($fleetUser, $organisationId, $userId, $branchService);

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

        if ($actorRole !== Roles::COMPANY_ADMIN && Roles::normalize($user->role ?? null) === Roles::COMPANY_ADMIN) {
            abort(403, 'Only company admins can manage company admin accounts.');
        }

        $branchesEnabled = $branchService->branchesEnabled() && $branchService->userBranchAccessEnabled();
        $branches = $branchesEnabled
            ? (Roles::bypassesBranchRestrictions($fleetUser)
                ? $branchService->getBranches($organisationId)
                : $branchService->getBranchesForUser($organisationId, (int) ($fleetUser['id'] ?? 0)))
            : collect();
        $selectedBranchIds = $branchesEnabled ? $branchService->getAccessibleBranchIdsForUser($organisationId, $userId) : [];

        return view('sharpfleet.admin.users.edit', [
            'user' => $user,
            'branchesEnabled' => $branchesEnabled,
            'branches' => $branches,
            'selectedBranchIds' => $selectedBranchIds,
        ]);
    }

    public function details(Request $request, int $userId)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || !Roles::canSetUserGroups($fleetUser)) {
            abort(403);
        }

        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);
        $actorRole = Roles::normalize($fleetUser['role'] ?? null);

        $branchService = new BranchService();
        $this->assertActorCanManageTargetUser($fleetUser, $organisationId, $userId, $branchService);

        $user = DB::connection('sharpfleet')
            ->table('users')
            ->select('id', 'first_name', 'last_name', 'email', 'role', 'is_driver')
            ->where('organisation_id', $organisationId)
            ->where('id', $userId)
            ->first();

        if (!$user) {
            abort(404);
        }

        if ($actorRole !== Roles::COMPANY_ADMIN && Roles::normalize($user->role ?? null) === Roles::COMPANY_ADMIN) {
            abort(403, 'Only company admins can manage company admin accounts.');
        }

        $settings = new CompanySettingsService($organisationId);
        $timezone = $settings->timezone();
        $dateFormat = $settings->dateFormat();
        $distanceUnit = $settings->distanceUnit();

        $actorBranchIds = $this->resolveActorBranchIds($fleetUser, $organisationId, $branchService);
        $branchRestricted = count($actorBranchIds) > 0 && Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id');

        $tripBase = DB::connection('sharpfleet')
            ->table('trips')
            ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
            ->where('trips.organisation_id', $organisationId)
            ->where('trips.user_id', $userId)
            ->whereNotNull('trips.started_at')
            ->when($branchRestricted, fn ($q) => $q->whereIn('vehicles.branch_id', $actorBranchIds));

        $totalTrips = (clone $tripBase)->count();

        $sumTotals = function (?string $start, ?string $end) use ($tripBase) {
            $query = clone $tripBase;
            if ($start) {
                $query->where('trips.started_at', '>=', $start);
            }
            if ($end) {
                $query->where('trips.started_at', '<=', $end);
            }

            $distance = (float) $query->clone()
                ->where(function ($q) {
                    $q->whereNull('vehicles.tracking_mode')->orWhere('vehicles.tracking_mode', '!=', 'hours');
                })
                ->whereNotNull('trips.start_km')
                ->whereNotNull('trips.end_km')
                ->selectRaw('SUM(CASE WHEN end_km >= start_km THEN end_km - start_km ELSE 0 END) as total')
                ->value('total');

            $hours = (float) $query->clone()
                ->where('vehicles.tracking_mode', 'hours')
                ->whereNotNull('trips.start_km')
                ->whereNotNull('trips.end_km')
                ->selectRaw('SUM(CASE WHEN end_km >= start_km THEN end_km - start_km ELSE 0 END) as total')
                ->value('total');

            return [$distance, $hours];
        };

        $now = Carbon::now($timezone);
        $weekStart = $now->copy()->startOfWeek();
        $monthStart = $now->copy()->startOfMonth();
        $yearStart = $now->copy()->startOfYear();

        [$totalDistance, $totalHours] = $sumTotals(null, null);
        [$weekDistance, $weekHours] = $sumTotals($weekStart->toDateTimeString(), $now->toDateTimeString());
        [$monthDistance, $monthHours] = $sumTotals($monthStart->toDateTimeString(), $now->toDateTimeString());
        [$yearDistance, $yearHours] = $sumTotals($yearStart->toDateTimeString(), $now->toDateTimeString());

        $convertDistance = function (float $value) use ($distanceUnit) {
            if ($distanceUnit === 'mi') {
                return $value * 0.621371;
            }
            return $value;
        };

        $lastTrip = (clone $tripBase)
            ->select(
                'trips.id',
                'trips.started_at',
                'trips.ended_at',
                'trips.start_km',
                'trips.end_km',
                'vehicles.tracking_mode',
                'vehicles.id as vehicle_id',
                'vehicles.name as vehicle_name',
                'vehicles.registration_number'
            )
            ->orderByDesc('trips.started_at')
            ->first();

        $topVehicles = (clone $tripBase)
            ->selectRaw('vehicles.id, vehicles.name, vehicles.registration_number, COUNT(*) as trip_count')
            ->groupBy('vehicles.id', 'vehicles.name', 'vehicles.registration_number')
            ->orderByDesc('trip_count')
            ->limit(5)
            ->get();

        return view('sharpfleet.admin.users.details', [
            'user' => $user,
            'dateFormat' => $dateFormat,
            'distanceUnit' => $distanceUnit,
            'totalTrips' => $totalTrips,
            'totals' => [
                'distance' => $convertDistance($totalDistance),
                'hours' => $totalHours,
                'week_distance' => $convertDistance($weekDistance),
                'week_hours' => $weekHours,
                'month_distance' => $convertDistance($monthDistance),
                'month_hours' => $monthHours,
                'year_distance' => $convertDistance($yearDistance),
                'year_hours' => $yearHours,
            ],
            'lastTrip' => $lastTrip,
            'topVehicles' => $topVehicles,
        ]);
    }

    public function update(Request $request, int $userId)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || !Roles::canSetUserGroups($fleetUser)) {
            abort(403);
        }

        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);

        $branchService = new BranchService();
        $this->assertActorCanManageTargetUser($fleetUser, $organisationId, $userId, $branchService);

        $target = DB::connection('sharpfleet')
            ->table('users')
            ->select('id', 'role')
            ->where('organisation_id', $organisationId)
            ->where('id', $userId)
            ->first();

        if (!$target) {
            abort(404);
        }

        $actorRole = Roles::normalize($fleetUser['role'] ?? null);
        $targetRole = Roles::normalize($target->role ?? null);

        // Branch admins cannot manage company admins.
        if ($actorRole !== Roles::COMPANY_ADMIN && $targetRole === Roles::COMPANY_ADMIN) {
            abort(403, 'Only company admins can manage company admin accounts.');
        }

        // Branch admins cannot change their own role (avoid lockouts).
        if ($actorRole !== Roles::COMPANY_ADMIN && (int) ($fleetUser['id'] ?? 0) === (int) $userId) {
            if ($request->has('role')) {
                return back()->withErrors(['role' => 'You cannot change your own group. Please contact a company administrator.']);
            }
        }

        $request->validate([
            'is_driver' => ['required', 'in:0,1'],
            'role' => ['nullable', 'string', 'max:50'],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['integer'],
            'revoke_mobile_tokens' => ['nullable', 'in:0,1'],
            'return_to' => ['nullable', 'string', 'max:255'],
        ]);

        // The form submits a hidden 0 plus a checkbox 1 when checked.
        // Read the resulting scalar value deterministically.
        $isDriver = ((int) $request->input('is_driver', 0) === 1) ? 1 : 0;

        $requestedRole = null;
        if ($request->has('role')) {
            $requestedRole = Roles::normalize((string) $request->input('role'));

            $allowed = $actorRole === Roles::COMPANY_ADMIN
                ? [Roles::COMPANY_ADMIN, Roles::BRANCH_ADMIN, Roles::BOOKING_ADMIN, Roles::DRIVER]
                : [Roles::BRANCH_ADMIN, Roles::BOOKING_ADMIN, Roles::DRIVER];

            if (!in_array($requestedRole, $allowed, true)) {
                return back()->withErrors(['role' => 'Invalid group selection.'])->withInput();
            }
        }

        $branchesEnabled = $branchService->branchesEnabled() && $branchService->userBranchAccessEnabled();

        // Persist branch access (schema-guarded). Defaults to the default branch if none provided.
        if ($branchesEnabled) {
            $actorBranchIds = $this->resolveActorBranchIds($fleetUser, $organisationId, $branchService);

            $incoming = $request->input('branch_ids', []);
            $incoming = is_array($incoming) ? $incoming : [];

            $selected = [];
            foreach ($incoming as $bid) {
                $bid = (int) $bid;
                if ($bid <= 0) {
                    continue;
                }

                // Non-company admins may only assign branches they themselves can access.
                if (!Roles::bypassesBranchRestrictions($fleetUser) && count($actorBranchIds) > 0 && !in_array($bid, $actorBranchIds, true)) {
                    continue;
                }

                if ($branchService->getBranch($organisationId, $bid)) {
                    $selected[$bid] = true;
                }
            }

            if (count($selected) === 0) {
                $defaultBranchId = 0;
                if (!Roles::bypassesBranchRestrictions($fleetUser) && count($actorBranchIds) > 0) {
                    $defaultBranchId = (int) $actorBranchIds[0];
                } else {
                    $defaultBranchId = (int) ($branchService->ensureDefaultBranch($organisationId) ?? 0);
                }
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
                // Only update role when explicitly provided by the UI.
                ...(isset($requestedRole) && $requestedRole !== null ? ['role' => $requestedRole] : []),
                'updated_at' => now(),
            ]);

        if ($updated === 0) {
            abort(404);
        }

        if ($isDriver === 0 || $request->boolean('revoke_mobile_tokens')) {
            $reason = $isDriver === 0 ? 'disable' : 'manual';
            $revoked = (new MobileTokenService())->revokeTokensForUser($organisationId, $userId, $reason === 'disable' ? 'driver_disabled' : 'manual_revoke');
            $this->logMobileTokenRevoked($request, $organisationId, $userId, $reason, $revoked);
        }

        // If the admin edited their own driver access, update the session so it takes effect immediately.
        if ((int) ($fleetUser['id'] ?? 0) === (int) $userId) {
            $request->session()->put('sharpfleet.user.is_driver', $isDriver);

            // Only company admins may change their own role, so this is safe.
            if (isset($requestedRole) && $requestedRole !== null) {
                $request->session()->put('sharpfleet.user.role', $requestedRole);
            }
        }

        $returnTo = (string) $request->input('return_to', '');
        if ($returnTo !== '' && str_starts_with($returnTo, '/app/sharpfleet/') && !str_contains($returnTo, '://') && !str_starts_with($returnTo, '//')) {
            return redirect($returnTo)->with('success', 'User updated.');
        }

        return redirect('/app/sharpfleet/admin/users')
            ->with('success', 'User updated.');
    }

    public function destroy(Request $request, int $userId)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || !Roles::canManageUsers($fleetUser)) {
            abort(403);
        }

        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);
        $currentUserId = (int) ($fleetUser['id'] ?? 0);
        $actorRole = Roles::normalize($fleetUser['role'] ?? null);

        $branchService = new BranchService();
        $this->assertActorCanManageTargetUser($fleetUser, $organisationId, $userId, $branchService);

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

        if ($actorRole !== Roles::COMPANY_ADMIN && Roles::normalize($user->role ?? null) === Roles::COMPANY_ADMIN) {
            abort(403, 'Only company admins can manage company admin accounts.');
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

        $revoked = (new MobileTokenService())->revokeTokensForUser($organisationId, $userId, 'archived');
        $this->logMobileTokenRevoked($request, $organisationId, $userId, 'archive', $revoked);

        return redirect('/app/sharpfleet/admin/users')
            ->with('success', 'Driver archived.');
    }

    public function unarchive(Request $request, int $userId)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || !Roles::canManageUsers($fleetUser)) {
            abort(403);
        }

        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);
        $actorRole = Roles::normalize($fleetUser['role'] ?? null);

        $branchService = new BranchService();
        $this->assertActorCanManageTargetUser($fleetUser, $organisationId, $userId, $branchService);

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

        if ($actorRole !== Roles::COMPANY_ADMIN && Roles::normalize($user->role ?? null) === Roles::COMPANY_ADMIN) {
            abort(403, 'Only company admins can manage company admin accounts.');
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

    public function revokeMobileTokens(Request $request, int $userId)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || !Roles::canManageUsers($fleetUser)) {
            abort(403);
        }

        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);
        $actorRole = Roles::normalize($fleetUser['role'] ?? null);
        $branchService = new BranchService();
        $this->assertActorCanManageTargetUser($fleetUser, $organisationId, $userId, $branchService);

        $target = DB::connection('sharpfleet')
            ->table('users')
            ->select('id', 'is_driver', 'archived_at', 'role')
            ->where('organisation_id', $organisationId)
            ->where('id', $userId)
            ->first();

        if (!$target) {
            abort(404);
        }

        if ($actorRole !== Roles::COMPANY_ADMIN && Roles::normalize($target->role ?? null) === Roles::COMPANY_ADMIN) {
            abort(403, 'Only company admins can manage company admin accounts.');
        }

        if ((int) ($target->is_driver ?? 0) !== 1 || !empty($target->archived_at)) {
            return redirect('/app/sharpfleet/admin/users/' . $userId . '/edit')
                ->withErrors(['error' => 'Mobile sessions can only be revoked for active driver-capable users.']);
        }

        $revoked = (new MobileTokenService())->revokeTokensForUser($organisationId, $userId, 'manual_revoke');
        $this->logMobileTokenRevoked($request, $organisationId, $userId, 'manual', $revoked);

        return redirect('/app/sharpfleet/admin/users/' . $userId . '/edit')
            ->with('success', 'Mobile sessions revoked.');
    }

    private function logMobileTokenRevoked(Request $request, int $organisationId, int $userId, string $reason, array $revoked): void
    {
        $count = (int) ($revoked['count'] ?? 0);
        if ($count <= 0) {
            return;
        }

        (new AuditLogService())->logSubscriber($request, 'mobile_token_revoked', [
            'target_user_id' => $userId,
            'revoke_reason' => $reason,
            'token_count_revoked' => $count,
            'device_ids' => $revoked['device_ids'] ?? [],
        ]);
    }
}
