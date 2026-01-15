<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BranchService;
use App\Services\SharpFleet\ReportingService;
use App\Services\SharpFleet\CompanySettingsService;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected ReportingService $reportingService;

    public function __construct(ReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
    }

    public function trips(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::canViewReports($user)) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Report type handling
        |--------------------------------------------------------------------------
        */
        $reportType = $request->input('report_type', 'general');

        if ($reportType === 'tax') {
            // Tax logbook = business only
            $request->merge([
                'trip_mode' => 'business',
            ]);
        }

        if ($reportType === 'care') {
            // Care reports require client-linked trips
            $request->merge([
                'require_customer' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Branch access enforcement
        |--------------------------------------------------------------------------
        */
        $branchesService = new BranchService();
        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($user);
        $branchesEnabled = $branchesService->branchesEnabled();
        $branchAccessEnabled = $branchesEnabled
            && $branchesService->vehiclesHaveBranchSupport()
            && $branchesService->userBranchAccessEnabled();

        $branchScopeEnabled = $branchAccessEnabled && !$bypassBranchRestrictions;

        $accessibleBranchIds = $branchScopeEnabled
            ? $branchesService->getAccessibleBranchIdsForUser(
                (int) $user['organisation_id'],
                (int) $user['id']
            )
            : [];

        if ($branchScopeEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        /*
        |--------------------------------------------------------------------------
        | CSV export (inherits report rules)
        |--------------------------------------------------------------------------
        */
        if ($request->export === 'csv') {
            return $this->reportingService->streamTripReportCsv(
                (int) $user['organisation_id'],
                $request,
                $user
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Build report data
        |--------------------------------------------------------------------------
        */
        $result = $this->reportingService->buildTripReport(
            (int) $user['organisation_id'],
            $request,
            $user
        );

        /*
        |--------------------------------------------------------------------------
        | Vehicles (respect branch scope)
        |--------------------------------------------------------------------------
        */
        $vehicles = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $user['organisation_id'])
            ->where('is_active', 1)
            ->when(
                $branchScopeEnabled && Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id'),
                fn ($q) => $q->whereIn('branch_id', $accessibleBranchIds)
            )
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Company settings (RAW, decoded for Blade)
        |--------------------------------------------------------------------------
        */
        $settings = [];

        $companySettingsRow = DB::connection('sharpfleet')
            ->table('company_settings')
            ->where('organisation_id', (int) $user['organisation_id'])
            ->first();

        if ($companySettingsRow && !empty($companySettingsRow->settings)) {
            $decoded = json_decode($companySettingsRow->settings, true);
            if (is_array($decoded)) {
                $settings = $decoded;
            }
        }

        $companySettingsService = new CompanySettingsService((int) $user['organisation_id']);

        /*
        |--------------------------------------------------------------------------
        | Render view
        |--------------------------------------------------------------------------
        */
        return view('sharpfleet.admin.reports.trips', [
            'trips' => $result['trips'],
            'totals' => $result['totals'],
            'applied' => $result['applied'],
            'ui' => $result['ui'],
            'branches' => $result['branches'] ?? collect(),
            'vehicles' => $vehicles,
            'customers' => $result['customers'],
            'hasCustomersTable' => (bool) ($result['hasCustomersTable'] ?? false),
            'customerLinkingEnabled' => (bool) ($result['customerLinkingEnabled'] ?? false),
            'companyTimezone' => (string) ($result['companyTimezone'] ?? 'UTC'),
            'purposeOfTravelEnabled' => (bool) $companySettingsService->purposeOfTravelEnabled(),

            // 🔑 SAME SOURCE AS start-trip.blade.php
            'settings' => $settings,
        ]);
    }

    public function vehicles()
    {
        // to be implemented later
    }
}
