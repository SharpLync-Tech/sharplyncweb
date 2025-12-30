<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\FaultService;
use App\Services\SharpFleet\CompanySettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        if (!$user || $user['role'] !== 'admin') {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) $user['organisation_id'];

        $settings = new CompanySettingsService($organisationId);
        $faultsEnabled = $settings->faultsEnabled();

        $faults = collect();
        if ($faultsEnabled) {
            $faults = $this->faultService->listFaultsForOrganisation($organisationId, 200);
        }

        return view('sharpfleet.admin.faults.index', [
            'faultsEnabled' => $faultsEnabled,
            'faults' => $faults,
        ]);
    }

    public function updateStatus(Request $request, $fault): RedirectResponse
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || $user['role'] !== 'admin') {
            abort(403, 'Admin access only');
        }

        $organisationId = (int) $user['organisation_id'];

        $settings = new CompanySettingsService($organisationId);
        if (!$settings->faultsEnabled()) {
            abort(403, 'Incident reporting is not enabled for this company.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,in_review,resolved,dismissed'],
        ]);

        $this->faultService->updateFaultStatus(
            $organisationId,
            (int) $fault,
            (string) $validated['status']
        );

        return back()->with('success', 'Fault status updated.');
    }
}
