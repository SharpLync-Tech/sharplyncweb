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

class UtilizationReportController extends Controller
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

        $scope = $request->input('scope', 'company'); // company | branch
        $branchId = $request->input('branch_id');
        $vehicleId = $request->input('vehicle_id');

        $period = $request->input('period', 'month'); // day | week | month
        $periodDate = $request->input('period_date');

        $availabilityPreset = $request->input('availability_preset', 'business_hours'); // business_hours | 24_7 | custom
        $availabilityDays = $request->input('availability_days', ['1', '2', '3', '4', '5']);
        $workStart = $request->input('work_start', '07:00');
        $workEnd = $request->input('work_end', '17:00');

        $baseDate = $periodDate
            ? Carbon::parse($periodDate, $companyTimezone)->startOfDay()
            : Carbon::now($companyTimezone)->startOfDay();

        if ($period === 'week') {
            $rangeStart = $baseDate->copy()->startOfWeek(Carbon::MONDAY);
            $rangeEnd = $baseDate->copy()->endOfWeek(Carbon::SUNDAY);
        } elseif ($period === 'day') {
            $rangeStart = $baseDate->copy()->startOfDay();
            $rangeEnd = $baseDate->copy()->endOfDay();
        } else {
            $rangeStart = $baseDate->copy()->startOfMonth();
            $rangeEnd = $baseDate->copy()->endOfMonth();
        }

        $startDate = $rangeStart->toDateString();
        $endDate = $rangeEnd->toDateString();

        $rangeSeconds = max(1, $rangeEnd->diffInSeconds($rangeStart));

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

        if ($scope === 'branch' && is_numeric($branchId) && Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id')) {
            $vehicleQuery->where('v.branch_id', (int) $branchId);
        }

        if (is_numeric($vehicleId)) {
            $vehicleQuery->where('v.id', (int) $vehicleId);
        }

        $vehicles = $vehicleQuery
            ->select([
                'v.id as vehicle_id',
                'v.name as vehicle_name',
                'v.registration_number',
                'v.branch_id as branch_id',
                DB::raw('COALESCE(b.name, "") as branch_name'),
                DB::raw('COUNT(t.id) AS trip_count'),
                DB::raw('SUM(
                    TIMESTAMPDIFF(SECOND, t.started_at, t.end_time)
                ) AS total_seconds'),
                DB::raw('MAX(t.started_at) AS last_used_at'),
            ])
            ->groupBy('v.id', 'v.name', 'v.registration_number', 'v.branch_id', 'branch_name')
            ->orderBy('v.name')
            ->get();

        $availabilitySeconds = $this->calculateAvailabilitySeconds(
            $rangeStart,
            $rangeEnd,
            $availabilityPreset,
            $availabilityDays,
            $workStart,
            $workEnd
        );

        $rows = $vehicles->map(function ($row) use ($companyTimezone, $dateFormat, $availabilitySeconds) {
            $totalSeconds = (int) ($row->total_seconds ?? 0);
            $tripCount = (int) ($row->trip_count ?? 0);

            $hours = (int) floor($totalSeconds / 3600);
            $minutes = (int) floor(($totalSeconds % 3600) / 60);
            $durationLabel = $totalSeconds > 0 ? ($hours . 'h ' . $minutes . 'm') : '0h 0m';

            if ($availabilitySeconds <= 0) {
                $utilization = 0.0;
            } else {
                $utilization = round(($totalSeconds / $availabilitySeconds) * 100, 1);
            }
            if ($utilization > 100) {
                $utilization = 100.0;
            }

            $availableHours = $availabilitySeconds > 0
                ? round($availabilitySeconds / 3600, 1)
                : 0.0;
            $usedHours = round($totalSeconds / 3600, 1);

            $lastUsed = $row->last_used_at
                ? Carbon::parse($row->last_used_at)->timezone($companyTimezone)->format($dateFormat)
                : null;

            return (object) [
                'vehicle_id' => (int) $row->vehicle_id,
                'vehicle_name' => (string) $row->vehicle_name,
                'registration_number' => (string) ($row->registration_number ?? ''),
                'branch_name' => (string) $row->branch_name,
                'trip_count' => $tripCount,
                'total_duration' => $durationLabel,
                'utilization_percent' => $utilization,
                'available_hours' => $availableHours,
                'used_hours' => $usedHours,
                'last_used_at' => $lastUsed,
            ];
        });

        $averageUtilization = $rows->count() > 0
            ? round($rows->avg('utilization_percent'), 1)
            : 0.0;

        $underUtilisedCount = $rows->filter(fn ($r) => $r->utilization_percent < 20)->count();
        $overUtilisedCount = $rows->filter(fn ($r) => $r->utilization_percent >= 85)->count();
        $totalUsedHours = round($rows->sum('used_hours'), 1);

        if ($request->input('export') === 'csv') {
            return $this->streamCsv(
                $rows,
                $period,
                $startDate,
                $endDate,
                $availabilityPreset,
                $availabilityDays,
                $workStart,
                $workEnd
            );
        }

        $branches = collect();
        if ($branchesEnabled) {
            $branches = $branchScopeEnabled
                ? $branchesService->getBranchesForUser($organisationId, (int) ($user['id'] ?? 0))
                : $branchesService->getBranches($organisationId);
        }

        $vehicleListQuery = DB::connection('sharpfleet')
            ->table('vehicles')
            ->select('id', 'name', 'registration_number', 'branch_id')
            ->where('organisation_id', $organisationId)
            ->where('is_active', 1);

        if ($branchScopeEnabled && Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id')) {
            $vehicleListQuery->whereIn('branch_id', $accessibleBranchIds);
        }

        if ($scope === 'branch' && is_numeric($branchId) && Schema::connection('sharpfleet')->hasColumn('vehicles', 'branch_id')) {
            $vehicleListQuery->where('branch_id', (int) $branchId);
        }

        $vehicleList = $vehicleListQuery
            ->orderBy('name')
            ->get();

        return view('sharpfleet.admin.reports.utilization', [
            'rows' => $rows,
            'branches' => $branches,
            'vehicles' => $vehicleList,
            'companyTimezone' => $companyTimezone,
            'dateFormat' => $dateFormat,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branchId' => $branchId,
            'scope' => $scope,
            'vehicleId' => $vehicleId,
            'period' => $period,
            'periodDate' => $periodDate,
            'availabilityPreset' => $availabilityPreset,
            'availabilityDays' => $availabilityDays,
            'workStart' => $workStart,
            'workEnd' => $workEnd,
            'averageUtilization' => $averageUtilization,
            'underUtilisedCount' => $underUtilisedCount,
            'overUtilisedCount' => $overUtilisedCount,
            'totalUsedHours' => $totalUsedHours,
        ]);
    }

    private function calculateAvailabilitySeconds(
        Carbon $rangeStart,
        Carbon $rangeEnd,
        string $preset,
        $days,
        string $workStart,
        string $workEnd
    ): int {
        if ($preset === '24_7') {
            return $rangeEnd->diffInSeconds($rangeStart);
        }

        $daySet = collect($days ?? [])
            ->map(fn ($d) => (int) $d)
            ->filter(fn ($d) => $d >= 0 && $d <= 6)
            ->unique()
            ->values()
            ->all();

        if ($preset === 'business_hours' || empty($daySet)) {
            $daySet = [1, 2, 3, 4, 5]; // Mon-Fri
        }

        $startParts = explode(':', $workStart);
        $endParts = explode(':', $workEnd);
        $startMinutes = ((int) ($startParts[0] ?? 0) * 60) + (int) ($startParts[1] ?? 0);
        $endMinutes = ((int) ($endParts[0] ?? 0) * 60) + (int) ($endParts[1] ?? 0);

        if ($endMinutes <= $startMinutes) {
            return $rangeEnd->diffInSeconds($rangeStart);
        }

        $totalSeconds = 0;
        $cursor = $rangeStart->copy()->startOfDay();
        $endDay = $rangeEnd->copy()->startOfDay();

        while ($cursor->lte($endDay)) {
            $dayIndex = $cursor->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
            $dayIndex = $dayIndex % 7; // 0 (Sun) - 6 (Sat)

            if (in_array($dayIndex, $daySet, true)) {
                $dayStart = $cursor->copy()->addMinutes($startMinutes);
                $dayEnd = $cursor->copy()->addMinutes($endMinutes);

                $windowStart = $dayStart->greaterThan($rangeStart) ? $dayStart : $rangeStart;
                $windowEnd = $dayEnd->lessThan($rangeEnd) ? $dayEnd : $rangeEnd;

                if ($windowEnd->greaterThan($windowStart)) {
                    $totalSeconds += $windowEnd->diffInSeconds($windowStart);
                }
            }

            $cursor->addDay();
        }

        return $totalSeconds;
    }

    private function streamCsv(
        $rows,
        string $period,
        string $startDate,
        string $endDate,
        string $preset,
        $days,
        string $workStart,
        string $workEnd
    ) {
        $filename = 'utilization-' . $period . '-' . $startDate . '-to-' . $endDate . '.csv';

        $dayMap = [
            1 => 'Mon',
            2 => 'Tue',
            3 => 'Wed',
            4 => 'Thu',
            5 => 'Fri',
            6 => 'Sat',
            0 => 'Sun',
        ];
        $daysList = collect($days ?? [])
            ->map(fn ($d) => (int) $d)
            ->map(fn ($d) => $dayMap[$d] ?? null)
            ->filter()
            ->unique()
            ->implode(', ');

        $availabilityLabel = match ($preset) {
            '24_7' => '24/7',
            'business_hours' => 'Business hours (Mon-Fri)',
            default => 'Custom (' . ($daysList !== '' ? $daysList : 'Mon-Fri') . ' ' . $workStart . '-' . $workEnd . ')',
        };

        return response()->streamDownload(function () use ($rows, $period, $startDate, $endDate, $availabilityLabel) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Period',
                'Date Range',
                'Availability',
                'Vehicle',
                'Registration',
                'Trips',
                'Used Hours',
                'Available Hours',
                'Utilisation %',
                'Last Used',
                'Status',
                'Recommendation',
            ]);

            foreach ($rows as $row) {
                $status = $row->utilization_percent < 20
                    ? 'Low'
                    : ($row->utilization_percent >= 85 ? 'Overused' : 'Healthy');

                $recommendation = $row->utilization_percent < 20
                    ? 'Consider reassigning or reducing idle time'
                    : ($row->utilization_percent >= 85 ? 'Review load or allocate more vehicles' : 'Healthy usage');

                fputcsv($out, [
                    ucfirst($period),
                    $startDate . ' to ' . $endDate,
                    $availabilityLabel,
                    $row->vehicle_name,
                    $row->registration_number,
                    $row->trip_count,
                    $row->used_hours,
                    $row->available_hours,
                    number_format($row->utilization_percent, 1),
                    $row->last_used_at ?? 'N/A',
                    $status,
                    $recommendation,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
