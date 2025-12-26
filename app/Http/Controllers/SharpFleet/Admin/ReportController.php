<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\ReportService;
use Illuminate\Support\Facades\DB;

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

        $trips = \Illuminate\Support\Facades\DB::connection('sharpfleet')
            ->table('trips')
            ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
            ->join('users', 'trips.user_id', '=', 'users.id')
            ->select(
                'trips.*',
                'vehicles.name as vehicle_name',
                'vehicles.registration_number',
                'vehicles.tracking_mode',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as driver_name")
            )
            ->where('trips.organisation_id', $user['organisation_id'])
            ->when($request->vehicle_id, fn($q) => $q->where('trips.vehicle_id', $request->vehicle_id))
            ->when($request->start_date, fn($q) => $q->where('trips.started_at', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->where('trips.started_at', '<=', $request->end_date . ' 23:59:59'))
            ->orderByDesc('trips.started_at')
            ->get();

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

        return view('sharpfleet.admin.reports.trips', compact('trips', 'vehicles'));
    }

    public function vehicles()
    {
        // $this->reportService->vehicleReport()
    }
}
