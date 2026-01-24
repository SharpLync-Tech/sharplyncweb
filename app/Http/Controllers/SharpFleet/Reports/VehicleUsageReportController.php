<?php

namespace App\Http\Controllers\SharpFleet\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VehicleUsageReportController extends Controller
{
    public function index(Request $request)
    {
        /*
        |----------------------------------------------------------------------
        | SharpFleet session auth (SINGLE SOURCE OF TRUTH)
        |----------------------------------------------------------------------
        */
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || !isset($user['organisation_id'])) {
            abort(403);
        }

        $organisationId = (int) $user['organisation_id'];
        $companyTimezone = $user['timezone'] ?? config('app.timezone');

        /*
        |----------------------------------------------------------------------
        | Regional date format (display only)
        |----------------------------------------------------------------------
        */
        $dateFormat = str_starts_with($companyTimezone, 'America/')
            ? 'm/d/Y'
            : 'd/m/Y';

        /*
        |----------------------------------------------------------------------
        | Inputs
        |----------------------------------------------------------------------
        */
        $scope     = $request->input('scope', 'company'); // company | branch
        $branchId  = $request->input('branch_id');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        /*
        |----------------------------------------------------------------------
        | Base trip query (organisation-scoped)
        |----------------------------------------------------------------------
        */
        $tripQuery = DB::connection('sharpfleet')
            ->table('trips')
            ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
            ->where('trips.organisation_id', $organisationId)
            ->whereNotNull('trips.started_at')
            ->whereNotNull('trips.end_time');

        /*
        |----------------------------------------------------------------------
        | Branch filter
        |----------------------------------------------------------------------
        */
        if ($scope === 'branch' && is_numeric($branchId)) {
            $tripQuery->where('trips.branch_id', (int) $branchId);
        }

        /*
        |----------------------------------------------------------------------
        | Date filtering (based on trip start, UTC-safe)
        |----------------------------------------------------------------------
        */
        if ($startDate) {
            $tripQuery->whereDate('trips.started_at', '>=', $startDate);
        }

        if ($endDate) {
            $tripQuery->whereDate('trips.started_at', '<=', $endDate);
        }

        /*
        |----------------------------------------------------------------------
        | Vehicle usage aggregation
        |----------------------------------------------------------------------
        */
        $vehicles = $tripQuery
            ->select([
                'vehicles.id as vehicle_id',
                'vehicles.name as vehicle_name',
                'vehicles.registration_number',

                DB::raw('COUNT(trips.id) AS trip_count'),

                DB::raw('SUM(
                    CASE
                        WHEN trips.start_km IS NOT NULL
                         AND trips.end_km IS NOT NULL
                         AND trips.end_km >= trips.start_km
                        THEN (trips.end_km - trips.start_km)
                        ELSE 0
                    END
                ) AS total_distance_km'),

                DB::raw('SUM(
                    TIMESTAMPDIFF(SECOND, trips.started_at, trips.end_time)
                ) AS total_seconds'),

                DB::raw('MAX(trips.started_at) AS last_used_at'),
            ])
            ->groupBy(
                'vehicles.id',
                'vehicles.name',
                'vehicles.registration_number'
            )
            ->orderByDesc('trip_count')
            ->get();

        /*
        |----------------------------------------------------------------------
        | Post-processing (timezone + UI-safe values)
        |----------------------------------------------------------------------
        */
        $vehicles = $vehicles->map(function ($v) use ($companyTimezone, $dateFormat) {
            $hours   = (int) floor($v->total_seconds / 3600);
            $minutes = (int) floor(($v->total_seconds % 3600) / 60);

            $lastUsedLocal = $v->last_used_at
                ? Carbon::parse($v->last_used_at)
                    ->timezone($companyTimezone)
                    ->format($dateFormat)
                : null;

            return (object) [
                'vehicle_id'          => (int) $v->vehicle_id,
                'vehicle_name'        => $v->vehicle_name,
                'registration_number' => $v->registration_number,

                'trip_count'          => (int) $v->trip_count,
                'total_distance_km'   => (int) $v->total_distance_km,
                'average_distance_km' => $v->trip_count > 0
                    ? round($v->total_distance_km / $v->trip_count, 1)
                    : 0,

                'total_duration'      => $hours . 'h ' . $minutes . 'm',
                'last_used_at'        => $lastUsedLocal,
            ];
        });

        /*
        |----------------------------------------------------------------------
        | CSV export (matches on-screen data)
        |----------------------------------------------------------------------
        */
        if ($request->input('export') === 'csv') {
            return $this->streamCsv($vehicles, $startDate, $endDate);
        }

        /*
        |----------------------------------------------------------------------
        | Summary totals
        |----------------------------------------------------------------------
        */
        $summary = [
            'vehicles' => $vehicles->count(),
            'trips'    => $vehicles->sum('trip_count'),
            'distance' => $vehicles->sum('total_distance_km') . ' km',
        ];

        /*
        |----------------------------------------------------------------------
        | Branch list (for selector)
        |----------------------------------------------------------------------
        */
        $branches = DB::connection('sharpfleet')
            ->table('branches')
            ->where('organisation_id', $organisationId)
            ->orderBy('name')
            ->get();

        /*
        |----------------------------------------------------------------------
        | Render
        |----------------------------------------------------------------------
        */
        return view('sharpfleet.admin.reports.vehicle-usage', [
            'vehicles'        => $vehicles,
            'summary'         => $summary,
            'branches'        => $branches,
            'companyTimezone' => $companyTimezone,
        ]);
    }

    private function streamCsv($vehicles, ?string $startDate, ?string $endDate)
    {
        $startLabel = $startDate ?: 'all';
        $endLabel = $endDate ?: 'all';
        $filename = 'vehicle-usage-' . $startLabel . '-to-' . $endLabel . '.csv';

        return response()->streamDownload(function () use ($vehicles) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Vehicle',
                'Registration',
                'Status',
                'Trips',
                'Total Distance',
                'Total Driving Time',
                'Average Distance / Trip',
                'Last Used',
            ]);

            foreach ($vehicles as $v) {
                $status = $v->trip_count === 0
                    ? 'Idle'
                    : ($v->trip_count >= 10 ? 'High' : 'Low');

                fputcsv($out, [
                    $v->vehicle_name,
                    $v->registration_number,
                    $status,
                    $v->trip_count,
                    $v->total_distance_km . ' km',
                    $v->total_duration,
                    $v->average_distance_km . ' km',
                    $v->last_used_at ?: 'N/A',
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
