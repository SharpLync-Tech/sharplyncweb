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
        |--------------------------------------------------------------------------
        | Inputs
        |--------------------------------------------------------------------------
        */
        $companyId = auth()->user()->company_id;

        $scope     = $request->input('scope', 'company'); // company | branch
        $branchId  = $request->input('branch_id');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        /*
        |--------------------------------------------------------------------------
        | Base query (trip-level)
        |--------------------------------------------------------------------------
        */
        $tripQuery = DB::table('sharpfleet_trips')
            ->where('company_id', $companyId)
            ->whereNotNull('started_at')
            ->whereNotNull('end_time');

        /*
        |--------------------------------------------------------------------------
        | Scope filtering
        |--------------------------------------------------------------------------
        */
        if ($scope === 'branch' && $branchId) {
            $tripQuery->where('branch_id', $branchId);
        }

        /*
        |--------------------------------------------------------------------------
        | Date filtering
        |--------------------------------------------------------------------------
        */
        if ($startDate) {
            $tripQuery->whereDate('started_at', '>=', $startDate);
        }

        if ($endDate) {
            $tripQuery->whereDate('started_at', '<=', $endDate);
        }

        /*
        |--------------------------------------------------------------------------
        | Vehicle usage aggregation
        |--------------------------------------------------------------------------
        */
        $vehicles = $tripQuery
            ->select([
                'vehicle_id',
                'vehicle_name',
                'registration_number',

                DB::raw('COUNT(*) AS trip_count'),

                DB::raw('SUM(
                    CASE
                        WHEN display_start IS NOT NULL
                         AND display_end IS NOT NULL
                         AND display_end >= display_start
                        THEN (display_end - display_start)
                        ELSE 0
                    END
                ) AS total_distance'),

                DB::raw('SUM(
                    TIMESTAMPDIFF(SECOND, started_at, end_time)
                ) AS total_seconds'),

                DB::raw('MAX(started_at) AS last_used_at'),
            ])
            ->groupBy('vehicle_id', 'vehicle_name', 'registration_number')
            ->orderByDesc('trip_count')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Post-processing (formatting for UI)
        |--------------------------------------------------------------------------
        */
        $vehicles = $vehicles->map(function ($v) {
            $hours   = floor($v->total_seconds / 3600);
            $minutes = floor(($v->total_seconds % 3600) / 60);

            return (object) [
                'vehicle_id'         => $v->vehicle_id,
                'vehicle_name'       => $v->vehicle_name,
                'registration_number'=> $v->registration_number,

                'trip_count'         => (int) $v->trip_count,
                'total_distance'     => number_format($v->total_distance, 0) . ' km',
                'total_duration'     => $hours . 'h ' . $minutes . 'm',
                'average_distance'   => $v->trip_count > 0
                    ? number_format($v->total_distance / $v->trip_count, 1) . ' km'
                    : '—',
                'last_used_at'       => $v->last_used_at,
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | Summary totals
        |--------------------------------------------------------------------------
        */
        $summary = [
            'vehicles' => $vehicles->count(),
            'trips'    => $vehicles->sum('trip_count'),
            'distance' => number_format(
                $vehicles->sum(fn ($v) => (int) filter_var($v->total_distance, FILTER_SANITIZE_NUMBER_INT)),
                0
            ) . ' km',
            'duration' => $vehicles->reduce(function ($carry, $v) {
                preg_match('/(\d+)h\s*(\d+)m/', $v->total_duration, $m);
                return $carry + (($m[1] ?? 0) * 3600) + (($m[2] ?? 0) * 60);
            }, 0),
        ];

        $summary['duration'] =
            floor($summary['duration'] / 3600) . 'h ' .
            floor(($summary['duration'] % 3600) / 60) . 'm';

        /*
        |--------------------------------------------------------------------------
        | Branch list (for selector)
        |--------------------------------------------------------------------------
        */
        $branches = DB::table('sharpfleet_branches')
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Render
        |--------------------------------------------------------------------------
        */
        return view('sharpfleet.admin.reports.vehicle-usage', [
            'vehicles'        => $vehicles,
            'summary'         => $summary,
            'branches'        => $branches,
            'companyTimezone' => auth()->user()->company_timezone,
        ]);
    }
}
