<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\ReportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function trips(\Illuminate\Http\Request $request)
    {
        $user = $request->session()->get('sharpfleet.user');

        if (!$user || $user['role'] !== 'admin') {
            abort(403, 'Admin access only');
        }

        $hasCustomersTable = Schema::connection('sharpfleet')->hasTable('customers');

        $customers = collect();
        if ($hasCustomersTable) {
            $customers = DB::connection('sharpfleet')
                ->table('customers')
                ->select('id', 'name')
                ->where('organisation_id', $user['organisation_id'])
                ->where('is_active', 1)
                ->orderBy('name')
                ->get();
        }

        $selectedCustomerName = null;
        if ($hasCustomersTable && $request->customer_id) {
            $selectedCustomerName = DB::connection('sharpfleet')
                ->table('customers')
                ->where('organisation_id', $user['organisation_id'])
                ->where('id', $request->customer_id)
                ->value('name');
        }

        $trips = \Illuminate\Support\Facades\DB::connection('sharpfleet')
            ->table('trips')
            ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
            ->join('users', 'trips.user_id', '=', 'users.id')
            ->when($hasCustomersTable, fn($q) => $q->leftJoin('customers', 'trips.customer_id', '=', 'customers.id'))
            ->select(
                'trips.*',
                'vehicles.name as vehicle_name',
                'vehicles.registration_number',
                'vehicles.tracking_mode',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as driver_name"),
                $hasCustomersTable
                    ? DB::raw('COALESCE(customers.name, trips.customer_name) as customer_name_display')
                    : DB::raw('trips.customer_name as customer_name_display')
            )
            ->where('trips.organisation_id', $user['organisation_id'])
            ->when($request->vehicle_id, fn($q) => $q->where('trips.vehicle_id', $request->vehicle_id))
            ->when($request->start_date, fn($q) => $q->where('trips.started_at', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->where('trips.started_at', '<=', $request->end_date . ' 23:59:59'))
            ->when($request->customer_id, function ($q) use ($request, $selectedCustomerName) {
                $q->where(function ($sub) use ($request, $selectedCustomerName) {
                    $sub->where('trips.customer_id', $request->customer_id);
                    if ($selectedCustomerName) {
                        $sub->orWhere('trips.customer_name', $selectedCustomerName);
                    }
                });
            })
            ->orderByDesc('trips.started_at')
            ->get();
        $totals = [
            'km' => 0.0,
            'hours' => 0.0,
        ];

        foreach ($trips as $trip) {
            if ($trip->end_km === null || $trip->start_km === null) {
                continue;
            }

            $delta = (float)$trip->end_km - (float)$trip->start_km;
            if ($delta < 0) {
                continue;
            }

            $unitKey = ($trip->tracking_mode ?? 'distance') === 'hours' ? 'hours' : 'km';
            $totals[$unitKey] += $delta;
        }

        if ($request->export === 'csv') {
            $headers = ['Vehicle', 'Rego', 'Driver', 'Trip Mode', 'Unit', 'Start Reading', 'End Reading', 'Client Present', 'Client Address', 'Started At', 'Ended At'];
            $callback = function() use ($trips, $headers) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $headers);
                foreach ($trips as $trip) {
                    $unit = ($trip->tracking_mode ?? 'distance') === 'hours' ? 'hours' : 'km';
                    fputcsv($file, [
                        $trip->vehicle_name,
                        $trip->registration_number,
                        $trip->driver_name,
                        $trip->trip_mode,
                        $unit,
                        $trip->start_km,
                        $trip->end_km,
                        $trip->client_present ? 'Yes' : 'No',
                        $trip->client_address ?? 'N/A',
                        $trip->started_at,
                        $trip->ended_at
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="trips_' . date('Y-m-d') . '.csv"'
            ]);
        }

        $vehicles = \Illuminate\Support\Facades\DB::connection('sharpfleet')
            ->table('vehicles')
            ->where('organisation_id', $user['organisation_id'])
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        return view('sharpfleet.admin.reports.trips', compact('trips', 'vehicles', 'customers', 'hasCustomersTable', 'totals'));
    }

    public function vehicles()
    {
        // $this->reportService->vehicleReport()
    }
}
