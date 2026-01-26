<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BranchService;
use App\Services\SharpFleet\ReportingService;
use App\Services\SharpFleet\CompanySettingsService;
use App\Support\SharpFleet\Roles;
use Barryvdh\DomPDF\Facade\Pdf;
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

    /**
     * --------------------------------------------------------------------------
     * Standard Trip Report (production)
     * --------------------------------------------------------------------------
     */
    public function trips(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::canViewReports($user)) {
            abort(403);
        }

        /*
        |----------------------------------------------------------------------
        | Company settings (SINGLE SOURCE OF TRUTH)
        |----------------------------------------------------------------------
        */
        $companySettings = new CompanySettingsService(
            (int) $user['organisation_id']
        );

        /*
        |----------------------------------------------------------------------
        | Report type handling
        |----------------------------------------------------------------------
        */
        $reportType = $request->input('report_type', 'general');

        if ($reportType === 'tax') {
            // Tax logbook = business only
            $request->merge([
                'trip_mode' => 'business',
            ]);
        }

        if ($reportType === 'care') {
            // Care reports require customer-linked trips
            $request->merge([
                'require_customer' => true,
            ]);
        }

        /*
        |----------------------------------------------------------------------
        | Branch access enforcement
        |----------------------------------------------------------------------
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
        |----------------------------------------------------------------------
        | CSV export (inherits report rules)
        |----------------------------------------------------------------------
        */
        if ($request->export === 'csv') {
            return $this->reportingService->streamTripReportCsv(
                (int) $user['organisation_id'],
                $request,
                $user
            );
        }

        /*
        |----------------------------------------------------------------------
        | Build report data
        |----------------------------------------------------------------------
        */
        $result = $this->reportingService->buildTripReport(
            (int) $user['organisation_id'],
            $request,
            $user
        );

        /*
        |----------------------------------------------------------------------
        | Vehicles (respect branch scope)
        |----------------------------------------------------------------------
        */
        $vehicles = DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', (int) $user['organisation_id'])
            ->where('is_active', 1)
            ->when(
                $branchScopeEnabled
                && Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id'),
                fn ($q) => $q->whereIn('branch_id', $accessibleBranchIds)
            )
            ->orderBy('name')
            ->get();

        /*
        |----------------------------------------------------------------------
        | Render production report view
        |----------------------------------------------------------------------
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

            // Time & formatting
            'companyTimezone' => (string) ($result['companyTimezone'] ?? $companySettings->timezone()),
            'purposeOfTravelEnabled' => (bool) $companySettings->purposeOfTravelEnabled(),

            // 🔑 Label resolved ONCE — same as start trip
            'clientPresenceLabel' => $companySettings->clientLabel(),
        ]);
    }

    /**
     * --------------------------------------------------------------------------
     * Standard Trip Report (PDF)
     * --------------------------------------------------------------------------
     */
    public function tripsPdf(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::canViewReports($user)) {
            abort(403);
        }

        $companySettings = new CompanySettingsService(
            (int) $user['organisation_id']
        );

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

        $result = $this->reportingService->buildTripReport(
            (int) $user['organisation_id'],
            $request,
            $user
        );

        $data = [
            'trips' => $result['trips'],
            'totals' => $result['totals'],
            'applied' => $result['applied'],
            'ui' => $result['ui'],
            'branches' => $result['branches'] ?? collect(),
            'customers' => $result['customers'],
            'hasCustomersTable' => (bool) ($result['hasCustomersTable'] ?? false),
            'customerLinkingEnabled' => (bool) ($result['customerLinkingEnabled'] ?? false),
            'companyTimezone' => (string) ($result['companyTimezone'] ?? $companySettings->timezone()),
            'purposeOfTravelEnabled' => (bool) $companySettings->purposeOfTravelEnabled(),
            'clientPresenceLabel' => $companySettings->clientLabel(),
        ];

        $pdf = Pdf::loadView('sharpfleet.admin.reports.trips-pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('sharpfleet_trips_report.pdf');
    }

    /**
     * --------------------------------------------------------------------------
     * Client Transport Report
     * --------------------------------------------------------------------------
     */
    public function clientTransport(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::canViewReports($user)) {
            abort(403);
        }

        $companySettings = new CompanySettingsService(
            (int) $user['organisation_id']
        );

        if ($request->export === 'csv') {
            return $this->reportingService->streamClientTransportCsv(
                (int) $user['organisation_id'],
                $request,
                $user
            );
        }

        $result = $this->reportingService->buildTripReport(
            (int) $user['organisation_id'],
            $request,
            $user
        );

        return view('sharpfleet.admin.reports.client-transport', [
            'trips' => $result['trips'],
            'applied' => $result['applied'],
            'ui' => $result['ui'],
            'branches' => $result['branches'] ?? collect(),
            'customers' => $result['customers'],
            'hasCustomersTable' => (bool) ($result['hasCustomersTable'] ?? false),
            'customerLinkingEnabled' => (bool) ($result['customerLinkingEnabled'] ?? false),

            // Time & formatting
            'companyTimezone' => (string) ($result['companyTimezone'] ?? $companySettings->timezone()),
            'purposeOfTravelEnabled' => (bool) $companySettings->purposeOfTravelEnabled(),
            'clientPresenceLabel' => $companySettings->clientLabel(),
        ]);
    }

    /**
     * --------------------------------------------------------------------------
     * Client Transport Report (PDF)
     * --------------------------------------------------------------------------
     */
    public function clientTransportPdf(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::canViewReports($user)) {
            abort(403);
        }

        $companySettings = new CompanySettingsService(
            (int) $user['organisation_id']
        );

        $result = $this->reportingService->buildTripReport(
            (int) $user['organisation_id'],
            $request,
            $user
        );

        $data = [
            'trips' => $result['trips'],
            'applied' => $result['applied'],
            'ui' => $result['ui'],
            'branches' => $result['branches'] ?? collect(),
            'customers' => $result['customers'],
            'hasCustomersTable' => (bool) ($result['hasCustomersTable'] ?? false),
            'customerLinkingEnabled' => (bool) ($result['customerLinkingEnabled'] ?? false),
            'companyTimezone' => (string) ($result['companyTimezone'] ?? $companySettings->timezone()),
            'purposeOfTravelEnabled' => (bool) $companySettings->purposeOfTravelEnabled(),
            'clientPresenceLabel' => $companySettings->clientLabel(),
        ];

        $pdf = Pdf::loadView('sharpfleet.admin.reports.client-transport-pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('sharpfleet_client_transport_report.pdf');
    }

    /**
     * --------------------------------------------------------------------------
     * Reports Home (cards)
     * --------------------------------------------------------------------------
     */
    public function home(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::canViewReports($user)) {
            abort(403);
        }

        $companySettings = new CompanySettingsService(
            (int) $user['organisation_id']
        );

        return view('sharpfleet.admin.reports.index', [
            'reportVisibility' => $companySettings->reportVisibility(),
        ]);
    }

    
    public function vehicles()
    {
        // to be implemented later
    }
}
