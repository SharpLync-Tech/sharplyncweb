<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BranchService;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserRightsController extends Controller
{
    public function index(Request $request)
    {
        $fleetUser = $request->session()->get('sharpfleet.user');

        if (!$fleetUser || !Roles::isCompanyAdmin($fleetUser)) {
            abort(403);
        }

        $organisationId = (int) ($fleetUser['organisation_id'] ?? 0);
        $selectedUserId = (int) $request->query('user_id', 0);

        $hasArchivedAt = Schema::connection('sharpfleet')->hasColumn('users', 'archived_at');

        $select = ['id', 'first_name', 'last_name', 'email', 'role', 'is_driver'];
        if ($hasArchivedAt) {
            $select[] = 'archived_at';
        }

        $userQuery = DB::connection('sharpfleet')
            ->table('users')
            ->select($select)
            ->where('organisation_id', $organisationId)
            ->where('email', 'not like', 'deleted+%@example.invalid')
            ->where(function ($q) {
                $q->whereNull('account_status')
                    ->orWhere('account_status', '!=', 'deleted');
            })
            ->orderByRaw("CASE WHEN role IN ('company_admin','admin') THEN 0 ELSE 1 END")
            ->orderBy('first_name')
            ->orderBy('last_name');

        $users = $userQuery->get();

        $selectedUser = null;
        if ($selectedUserId > 0) {
            $selectedUser = $users->firstWhere('id', $selectedUserId);
        }

        $branchService = new BranchService();
        $branchesEnabled = $branchService->branchesEnabled();
        $branchAccessEnabled = $branchesEnabled && $branchService->userBranchAccessEnabled();
        $branches = $branchesEnabled ? $branchService->getBranches($organisationId) : collect();
        $selectedBranchIds = ($branchAccessEnabled && $selectedUser)
            ? $branchService->getAccessibleBranchIdsForUser($organisationId, (int) $selectedUser->id)
            : [];

        return view('sharpfleet.admin.users.user-rights', [
            'users' => $users,
            'selectedUser' => $selectedUser,
            'branchesEnabled' => $branchesEnabled,
            'branchAccessEnabled' => $branchAccessEnabled,
            'branches' => $branches,
            'selectedBranchIds' => $selectedBranchIds,
        ]);
    }
}
