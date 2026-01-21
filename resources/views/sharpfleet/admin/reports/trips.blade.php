@extends('layouts.sharpfleet')

@section('title', 'Compliance & Trip Reports')

@section('sharpfleet-content')

@php
    use Carbon\Carbon;

    /*
    |--------------------------------------------------------------------------
    | Inputs resolved by controller (single source of truth)
    |--------------------------------------------------------------------------
    */
    $companyTimezone      = $companyTimezone ?? config('app.timezone');
    $clientPresenceLabel  = trim((string) ($clientPresenceLabel ?? 'Client'));
    $clientPresenceLabel  = $clientPresenceLabel !== '' ? $clientPresenceLabel : 'Client';

    /*
    |--------------------------------------------------------------------------
    | UI state
    |--------------------------------------------------------------------------
    */
    $uiVehicleId = $ui['vehicle_id'] ?? request('vehicle_id');
    $uiStartDate = $ui['start_date'] ?? request('start_date');
    $uiEndDate   = $ui['end_date'] ?? request('end_date');

    /*
    |--------------------------------------------------------------------------
    | Totals
    |--------------------------------------------------------------------------
    */
    $totalTrips    = $trips->count();
    $businessTrips = $trips->where('trip_mode', 'business')->count();
    $privateTrips  = $trips->where('trip_mode', 'private')->count();

    /*
    |--------------------------------------------------------------------------
    | Date formatting (display only)
    |--------------------------------------------------------------------------
    */
    if (str_starts_with($companyTimezone, 'America/')) {
        $dateFormat = 'm/d/Y';
    } else {
        $dateFormat = 'd/m/Y';
    }

    $displayStartDate = $uiStartDate
        ? Carbon::parse($uiStartDate)->timezone($companyTimezone)->format($dateFormat)
        : '—';

    $displayEndDate = $uiEndDate
        ? Carbon::parse($uiEndDate)->timezone($companyTimezone)->format($dateFormat)
        : '—';

    $branchesEnabled = isset($branches) && $branches->count() > 0;
@endphp

<div class="container">

    {{-- ================= HEADER ================= --}}
    <div class="page-header mb-3">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Compliance & Trip Reports</h1>
                <p class="page-description">
                    Generate structured reports for tax, care compliance, and internal review.
                </p>
            </div>

            <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}">
                <input type="hidden" name="export" value="csv">
                <input type="hidden" name="vehicle_id" value="{{ $uiVehicleId }}">
                <input type="hidden" name="start_date" value="{{ $uiStartDate }}">
                <input type="hidden" name="end_date" value="{{ $uiEndDate }}">
                <button type="submit" class="btn btn-primary">
                    Export Report (CSV)
                </button>
            </form>
        </div>
    </div>

    {{-- ================= REPORT OPTIONS ================= --}}
    <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}">
        <div class="card mb-3">
            <div class="card-body">

                <h3 class="mb-4">Reports</h3>

                <div class="grid grid-3 gap-4">

                    {{-- Scope --}}
                    <div>
                        <h5 class="mb-2">Scope</h5>

                        <label class="d-block mb-1">
                            <input type="radio" name="scope" value="company" checked>
                            Company-wide
                        </label>

                        @if($branchesEnabled)
                            <label class="d-block">
                                <input type="radio" name="scope" value="branch">
                                Branch only
                            </label>
                        @endif
                    </div>

                    {{-- Filters --}}
                    <div>
                        <h5 class="mb-2">Filter by</h5>

                        <label class="d-block mb-1">
                            <input type="checkbox" name="filter_drivers">
                            Drivers
                        </label>

                        <label class="d-block">
                            <input type="checkbox" name="filter_vehicles">
                            Vehicles
                        </label>
                    </div>

                    {{-- Screen columns --}}
                    <div>
                        <h5 class="mb-2">Show on screen</h5>

                        <label class="d-block mb-1">
                            <input type="checkbox" name="show_registration">
                            Registration number
                        </label>

                        <label class="d-block mb-1">
                            <input type="checkbox" name="show_purpose">
                            Purpose of travel
                        </label>

                        <label class="d-block mb-1">
                            <input type="checkbox" name="show_distance">
                            Distance
                        </label>

                        <label class="d-block mb-1">
                            <input type="checkbox" name="show_duration">
                            Duration
                        </label>

                        <label class="d-block">
                            <input type="checkbox" name="show_times">
                            Start & end times
                        </label>
                    </div>

                </div>

                <div class="flex-between mt-4">
                    <div class="text-muted small">
                        Screen options affect on-screen display only. CSV export always includes full data.
                    </div>

                    <button type="submit" class="btn btn-secondary">
                        Update Report
                    </button>
                </div>

            </div>
        </div>
    </form>

    {{-- ================= SUMMARY ================= --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="grid grid-4 text-center">
                <div>
                    <strong>{{ $totalTrips }}</strong><br>
                    <span class="text-muted small">Trips</span>
                </div>
                <div>
                    <strong>{{ $businessTrips }}</strong><br>
                    <span class="text-muted small">Business</span>
                </div>
                <div>
                    <strong>{{ $privateTrips }}</strong><br>
                    <span class="text-muted small">Private</span>
                </div>
                <div>
                    <strong>{{ $displayStartDate }} → {{ $displayEndDate }}</strong><br>
                    <span class="text-muted small">Reporting period</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= RESULTS ================= --}}
    <div class="card">
        <div class="card-body">

            @if($trips->count() === 0)
                <p class="text-muted fst-italic">
                    No trips found for the selected filters.
                </p>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Driver</th>
                                <th>Business / Private</th>
                                <th>{{ $clientPresenceLabel }}</th>
                                <th>Started</th>
                                <th>Ended</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trips as $t)
                                <tr>
                                    <td class="fw-bold">
                                        {{ $t->vehicle_name }}<br>
                                        <small class="text-muted">{{ $t->registration_number }}</small>
                                    </td>
                                    <td>{{ $t->driver_name }}</td>
                                    <td>{{ strtolower($t->trip_mode) === 'private' ? 'Private' : 'Business' }}</td>
                                    <td>{{ $t->customer_name_display ?: '—' }}</td>
                                    <td>
                                        {{ Carbon::parse($t->started_at)->timezone($companyTimezone)->format($dateFormat) }}
                                    </td>
                                    <td>
                                        {{ $t->end_time
                                            ? Carbon::parse($t->end_time)->timezone($companyTimezone)->format($dateFormat)
                                            : '—'
                                        }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>

</div>

@endsection
