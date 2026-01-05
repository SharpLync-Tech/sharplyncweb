<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\FaultService;
use App\Services\SharpFleet\BranchService;
use App\Services\SharpFleet\CompanySettingsService;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FaultController extends Controller
{
    protected FaultService $faultService;

    public function __construct(FaultService $faultService)
    {
        $this->faultService = $faultService;
    }

    public function index(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::canManageFaults($user)) {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) $user['organisation_id'];

        $branchService = new BranchService();
        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($user);
        $branchScopeEnabled = !$bypassBranchRestrictions
            && $branchService->branchesEnabled()
            && $branchService->vehiclesHaveBranchSupport()
            && $branchService->userBranchAccessEnabled();
        $accessibleBranchIds = $branchScopeEnabled
            ? $branchService->getAccessibleBranchIdsForUser($organisationId, (int) $user['id'])
            : [];
        if ($branchScopeEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        $settings = new CompanySettingsService($organisationId);
        $faultsEnabled = $settings->faultsEnabled();
        $companyTimezone = $settings->timezone();

        $faults = collect();
        if ($faultsEnabled) {
            $faults = $this->faultService->listFaultsForOrganisation(
                $organisationId,
                200,
                $branchScopeEnabled ? $accessibleBranchIds : null
            );
        }

        return view('sharpfleet.admin.faults.index', [
            'faultsEnabled' => $faultsEnabled,
            'faults' => $faults,
            'companyTimezone' => $companyTimezone,
        ]);
    }

    public function updateStatus(Request $request, $fault): RedirectResponse
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::canManageFaults($user)) {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) $user['organisation_id'];

        $settings = new CompanySettingsService($organisationId);
        if (!$settings->faultsEnabled()) {
            abort(403, 'Vehicle issue/accident reporting is not enabled for this company.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,in_review,resolved,dismissed,archived'],
        ]);

        // Branch admins may only update faults for vehicles in their branch(es).
        $branchService = new BranchService();
        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($user);
        $branchScopeEnabled = !$bypassBranchRestrictions
            && $branchService->branchesEnabled()
            && $branchService->vehiclesHaveBranchSupport()
            && $branchService->userBranchAccessEnabled();
        $accessibleBranchIds = $branchScopeEnabled
            ? $branchService->getAccessibleBranchIdsForUser($organisationId, (int) $user['id'])
            : [];
        if ($branchScopeEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        if ($branchScopeEnabled && Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id')) {
            $allowed = DB::connection('sharpfleet')
                ->table('faults as f')
                ->join('vehicles as v', 'f.vehicle_id', '=', 'v.id')
                ->where('f.organisation_id', $organisationId)
                ->where('f.id', (int) $fault)
                ->whereIn('v.branch_id', $accessibleBranchIds)
                ->exists();

            if (!$allowed) {
                abort(403, 'You can only manage faults for your branch.');
            }
        }

        $this->faultService->updateFaultStatus(
            $organisationId,
            (int) $fault,
            (string) $validated['status']
        );

        return back()->with('success', 'Fault status updated.');
    }
}
