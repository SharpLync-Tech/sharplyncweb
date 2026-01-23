<?php

namespace App\Http\Controllers\SharpFleet\Reports;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BranchService;
use App\Services\SharpFleet\CompanySettingsService;
use App\Support\SharpFleet\Roles;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FleetManagerOperationalReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !Roles::canViewReports($user)) {
            abort(403);
        }

        $organisationId = (int) ($user['organisation_id'] ?? 0);
        if ($organisationId <= 0) {
            abort(403, 'Missing organisation');
        }

        $settingsService = new CompanySettingsService($organisationId);
        $companyTimezone = $settingsService->timezone();

        $branchesService = new BranchService();
        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($user);
        $branchesEnabled = $branchesService->branchesEnabled();
        $branchAccessEnabled = $branchesEnabled
            && $branchesService->vehiclesHaveBranchSupport()
            && $branchesService->userBranchAccessEnabled();
        $branchScopeEnabled = $branchAccessEnabled && !$bypassBranchRestrictions;

        $accessibleBranchIds = $branchScopeEnabled
            ? $branchesService->getAccessibleBranchIdsForUser(
                $organisationId,
                (int) ($user['id'] ?? 0)
            )
            : [];

        if ($branchScopeEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        $dateFormat = str_starts_with($companyTimezone, 'America/') ? 'm/d/Y' : 'd/m/Y';

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$startDate || !$endDate) {
            $end = Carbon::now($companyTimezone)->startOfDay();
            $start = $end->copy()->subDays(6);
            $startDate = $start->toDateString();
            $endDate = $end->toDateString();
        }

        $statusFilter = $request->input('status', 'all'); // all | active | inactive
        $branchId = $request->input('branch_id');

        $vehicleQuery = DB::connection('sharpfleet')
            ->table('vehicles as v')
            ->leftJoin('branches as b', 'v.branch_id', '=', 'b.id')
            ->leftJoin('trips as t', function ($join) use ($organisationId, $startDate, $endDate) {
                $join->on('t.vehicle_id', '=', 'v.id')
                    ->where('t.organisation_id', '=', $organisationId)
                    ->whereNotNull('t.started_at')
                    ->whereNotNull('t.end_time');

                if ($startDate) {
                    $join->whereDate('t.started_at', '>=', $startDate);
                }
                if ($endDate) {
                    $join->whereDate('t.started_at', '<=', $endDate);
                }
            })
            ->where('v.organisation_id', $organisationId)
            ->where('v.is_active', 1);

        if ($branchScopeEnabled && Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id')) {
            $vehicleQuery->whereIn('v.branch_id', $accessibleBranchIds);
        }

        if (is_numeric($branchId) && Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id')) {
            $vehicleQuery->where('v.branch_id', (int) $branchId);
        }

        $vehicles = $vehicleQuery
            ->select([
                'v.id as vehicle_id',
                'v.name as vehicle_name',
                'v.branch_id as branch_id',
                DB::raw('COALESCE(b.name, "") as branch_name'),
                DB::raw('COUNT(t.id) AS trip_count'),
                DB::raw('SUM(
                    CASE
                        WHEN t.start_km IS NOT NULL
                         AND t.end_km IS NOT NULL
                         AND t.end_km >= t.start_km
                        THEN (t.end_km - t.start_km)
                        ELSE 0
                    END
                ) AS total_distance'),
                DB::raw('SUM(
                    TIMESTAMPDIFF(SECOND, t.started_at, t.end_time)
                ) AS total_seconds'),
                DB::raw('MAX(t.started_at) AS last_used_at'),
            ])
            ->groupBy('v.id', 'v.name', 'v.branch_id', 'branch_name')
            ->orderBy('v.name')
            ->get();

        $rows = $vehicles->map(function ($row) use ($settingsService, $companyTimezone, $dateFormat) {
            $tripCount = (int) ($row->trip_count ?? 0);
            $totalDistance = (float) ($row->total_distance ?? 0);
            $totalSeconds = (int) ($row->total_seconds ?? 0);

            $hours = (int) floor($totalSeconds / 3600);
            $minutes = (int) floor(($totalSeconds % 3600) / 60);
            $durationLabel = $totalSeconds > 0 ? ($hours . 'h ' . $minutes . 'm') : '0h 0m';

            $avgDistance = $tripCount > 0 ? round($totalDistance / $tripCount, 1) : 0;
            $branchId = (int) ($row->branch_id ?? 0);
            $unit = $settingsService->distanceUnitForBranch($branchId > 0 ? $branchId : null);
            $unitLabel = $unit === 'mi' ? 'mi' : 'km';

            $lastUsed = $row->last_used_at
                ? Carbon::parse($row->last_used_at)->timezone($companyTimezone)->format($dateFormat)
                : null;

            $active = $tripCount > 0;

            return (object) [
                'vehicle_id' => (int) $row->vehicle_id,
                'vehicle_name' => (string) $row->vehicle_name,
                'branch_name' => (string) $row->branch_name,
                'trip_count' => $tripCount,
                'total_distance' => $totalDistance,
                'total_distance_label' => number_format($totalDistance, 1) . ' ' . $unitLabel,
                'total_duration' => $durationLabel,
                'average_distance' => $avgDistance,
                'average_distance_label' => number_format($avgDistance, 1) . ' ' . $unitLabel,
                'last_used_at' => $lastUsed,
                'status' => $active ? 'Active' : 'Inactive',
            ];
        });

        if ($statusFilter === 'active') {
            $rows = $rows->filter(fn ($r) => $r->trip_count > 0)->values();
        } elseif ($statusFilter === 'inactive') {
            $rows = $rows->filter(fn ($r) => $r->trip_count === 0)->values();
        }

        if ($request->input('export') === 'csv') {
            return $this->streamCsv($rows, $startDate, $endDate);
        }

        $branches = collect();
        if ($branchesEnabled) {
            $branches = $branchScopeEnabled
                ? $branchesService->getBranchesForUser($organisationId, (int) ($user['id'] ?? 0))
                : $branchesService->getBranches($organisationId);
        }

        return view('sharpfleet.admin.reports.fleet-manager-operational', [
            'rows' => $rows,
            'branches' => $branches,
            'companyTimezone' => $companyTimezone,
            'dateFormat' => $dateFormat,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'statusFilter' => $statusFilter,
            'branchId' => $branchId,
        ]);
    }

    private function streamCsv($rows, string $startDate, string $endDate)
    {
        $filename = 'fleet-manager-operational-' . $startDate . '-to-' . $endDate . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Vehicle',
                'Branch',
                'Trip Count',
                'Total Distance',
                'Total Duration',
                'Average Distance / Trip',
                'Last Used',
                'Status',
            ]);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->vehicle_name,
                    $row->branch_name,
                    $row->trip_count,
                    $row->total_distance_label,
                    $row->total_duration,
                    $row->average_distance_label,
                    $row->last_used_at ?? 'N/A',
                    $row->status,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
