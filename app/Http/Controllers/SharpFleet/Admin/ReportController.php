<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BranchService;
use App\Services\SharpFleet\ReportingService;
use App\Services\SharpFleet\CompanySettingsService;
use App\Support\SharpFleet\Roles;
use Illuminate\Support\Facades\Schema;

class ReportingService
{
    public function buildTripReport(int $organisationId, Request $request, array $user): array
    {
        // -----------------------------------------
        // Base query
        // -----------------------------------------
        $query = DB::connection('sharpfleet')
            ->table('trips as t')
            ->leftJoin('customers as c', 'c.id', '=', 't.customer_id')
            ->leftJoin('vehicles as v', 'v.id', '=', 't.vehicle_id')
            ->leftJoin('drivers as d', 'd.id', '=', 't.driver_id');

        // -----------------------------------------
        // Filters (simplified – keep your existing ones)
        // -----------------------------------------
        if ($request->filled('vehicle_id')) {
            $query->where('t.vehicle_id', (int) $request->vehicle_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('t.started_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('t.started_at', '<=', $request->end_date);
        }

        if ($request->filled('customer_id')) {
            $query->where('t.customer_id', (int) $request->customer_id);
        }

        // -----------------------------------------
        // 🔥 SELECT – THIS IS THE IMPORTANT PART
        // -----------------------------------------
        $trips = $query->select([
                't.id',

                // 🔴 🔴 🔴 THIS WAS MISSING 🔴 🔴 🔴
                't.customer_id',

                't.vehicle_id',
                't.driver_id',

                't.vehicle_name',
                't.driver_name',

                't.trip_mode',
                't.started_at',
                't.end_time',
                't.purpose_of_travel',

                // Display logic stays exactly as before
                DB::raw('COALESCE(c.name, t.customer_name_display) as customer_name_display'),

                'v.registration_number',
            ])
            ->where('t.organisation_id', $organisationId)
            ->orderByDesc('t.started_at')
            ->get();

        // -----------------------------------------
        // Totals (simplified example)
        // -----------------------------------------
        $totals = [
            'distance_km' => 0,
            'distance_mi' => 0,
            'hours'       => 0,
        ];

        // -----------------------------------------
        // UI / Applied metadata (simplified)
        // -----------------------------------------
        $applied = [
            'vehicle_label' => $request->vehicle_id ? 'Filtered vehicle' : 'All vehicles',
            'customer_label' => $request->customer_id ? 'Filtered customer' : 'All customers',
        ];

        $ui = [
            'vehicle_id' => $request->vehicle_id,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'customer_id' => $request->customer_id,
        ];

        // -----------------------------------------
        // Customers (for filter dropdown)
        // -----------------------------------------
        $hasCustomersTable = Schema::connection('sharpfleet')->hasTable('customers');

        $customers = collect();

        if ($hasCustomersTable) {
            $customers = DB::connection('sharpfleet')
                ->table('customers')
                ->where('organisation_id', $organisationId)
                ->orderBy('name')
                ->get();
        }

        // -----------------------------------------
        // Final payload
        // -----------------------------------------
        return [
            'trips' => $trips,
            'totals' => $totals,
            'applied' => $applied,
            'ui' => $ui,
            'customers' => $customers,
            'hasCustomersTable' => $hasCustomersTable,
            'customerLinkingEnabled' => true,
            'companyTimezone' => config('app.timezone'),
        ];
    }
}
