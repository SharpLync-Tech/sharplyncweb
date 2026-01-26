@extends('layouts.sharpfleet')

@section('title', 'Trips & Compliance Report')

@section('sharpfleet-content')

@php
    use Carbon\Carbon;

    /*
    |--------------------------------------------------------------------------
    | Core inputs (resolved by controller)
    |--------------------------------------------------------------------------
    */
    $companyTimezone = $companyTimezone ?? config('app.timezone');

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
    | Date formatting (display only)
    |--------------------------------------------------------------------------
    */
    $dateFormat = str_starts_with($companyTimezone, 'America/')
        ? 'm/d/Y'
        : 'd/m/Y';

    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */
    $totalTrips = $trips->count();
@endphp

<div class="container">

    {{-- ================= HEADER ================= --}}
    <div class="page-header mb-3">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Trips & Compliance Report</h1>
                <p class="page-description">
                    Detailed trip-level vehicle usage report suitable for regulatory compliance,
                    accountant review, and audit evidence.
                </p>
            </div>

            <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}">
                <input type="hidden" name="export" value="csv">
                <input type="hidden" name="vehicle_id" value="{{ $uiVehicleId }}">
                <input type="hidden" name="start_date" value="{{ $uiStartDate }}">
                <input type="hidden" name="end_date" value="{{ $uiEndDate }}">
                <button type="submit" class="btn btn-primary">
                    Export CSV
                </button>
            </form>
        </div>
    </div>

    {{-- ================= FILTERS ================= --}}
    <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}">
        <div class="card mb-3">
            <div class="card-body">

                <div class="grid grid-3 gap-4">

                    {{-- Scope --}}
                    <div>
                        <h5 class="mb-2">Scope</h5>
                        <label class="d-block">
                            <input type="radio" name="scope" value="company" checked>
                            Company-wide
                        </label>
                    </div>

                    {{-- Filters --}}
                    <div>
                        <h5 class="mb-2">Filters</h5>

                        <label class="d-block mb-1">
                            Vehicle
                        </label>

                        <label class="d-block">
                            Date range
                        </label>
                    </div>

                    {{-- Compliance note --}}
                    <div>
                        <h5 class="mb-2">Compliance format</h5>
                        <p class="text-muted small mb-0">
                            This report includes full trip detail in a fixed layout
                            designed to support regulatory and financial review.
                        </p>
                    </div>

                </div>

                <div class="flex-between mt-4">
                    <div class="text-muted small">
                        On-screen view matches exported data.
                        No columns are omitted in the CSV export.
                    </div>

                    <button type="submit" class="btn btn-outline-primary">
                        Update Report
                    </button>
                </div>

            </div>
        </div>
    </form>

    {{-- ================= SUMMARY ================= --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="grid grid-3 text-center">
                <div>
                    <strong>{{ $totalTrips }}</strong><br>
                    <span class="text-muted small">Trips in report</span>
                </div>
                <div>
                    <strong>{{ $uiStartDate ?: '—' }} → {{ $uiEndDate ?: '—' }}</strong><br>
                    <span class="text-muted small">Reporting period</span>
                </div>
                <div>
                    <strong>Trip-level detail</strong><br>
                    <span class="text-muted small">Audit-ready format</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= RESULTS ================= --}}
    <div class="card">
        <div class="card-body">

            @if($trips->count() === 0)
                <p class="text-muted fst-italic">
                    No trips found for the selected period.
                </p>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Vehicle</th>
                                <th>Registration</th>
                                <th>Driver</th>
                                <th>Client / Customer</th>
                                <th>Purpose of travel</th>
                                <th class="text-end">Distance</th>
                                <th class="text-end">Duration</th>
                                <th>Start time</th>
                                <th>End time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trips as $t)
                                <tr>
                                    <td>
                                        {{ Carbon::parse($t->started_at)->timezone($companyTimezone)->format($dateFormat) }}
                                    </td>

                                    <td class="fw-bold">
                                        {{ $t->vehicle_name }}
                                    </td>

                                    <td>{{ $t->registration_number ?: '—' }}</td>

                                    <td>{{ $t->driver_name ?: '—' }}</td>

                                    <td>{{ $t->customer_name_display ?: '—' }}</td>

                                    <td>{{ $t->purpose_of_travel ?: '—' }}</td>

                                    <td class="text-end">{{ $t->distance_label ?? '—' }}</td>

                                    <td class="text-end">{{ $t->duration_label ?? '—' }}</td>

                                    <td>
                                        {{ $t->started_at
                                            ? Carbon::parse($t->started_at)->timezone($companyTimezone)->format('H:i')
                                            : '—' }}
                                    </td>

                                    <td>
                                        {{ $t->end_time
                                            ? Carbon::parse($t->end_time)->timezone($companyTimezone)->format('H:i')
                                            : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="text-muted small mt-3 text-center">
        This report is system-generated and reflects recorded trip data
        at the time of export.
    </div>

</div>

@endsection
