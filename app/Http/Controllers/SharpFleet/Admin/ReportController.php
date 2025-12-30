<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\ReportingService;

class ReportController extends Controller
{
    protected ReportingService $reportingService;

    public function __construct(ReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
    }

    public function trips(\Illuminate\Http\Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || $user['role'] !== 'admin') {
            abort(403, 'Admin access only');
        }

        if ($request->export === 'csv') {
            return $this->reportingService->streamTripReportCsv((int) $user['organisation_id'], $request);
        }

        $result = $this->reportingService->buildTripReport((int) $user['organisation_id'], $request);

        $vehicles = \Illuminate\Support\Facades\DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $user['organisation_id'])
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        return view('sharpfleet.admin.reports.trips', [
            'trips' => $result['trips'],
            'totals' => $result['totals'],
            'applied' => $result['applied'],
            'ui' => $result['ui'],
            'vehicles' => $vehicles,
            'customers' => $result['customers'],
            'hasCustomersTable' => (bool) ($result['hasCustomersTable'] ?? false),
            'customerLinkingEnabled' => (bool) ($result['customerLinkingEnabled'] ?? false),
            'companyTimezone' => (string) ($result['companyTimezone'] ?? 'UTC'),
        ]);
    }

    public function vehicles()
    {
        // to be implemented later
    }
}
