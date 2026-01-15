@extends('layouts.sharpfleet')

@section('title', 'Compliance & Trip Reports')

@section('sharpfleet-content')

@php
    $companyTimezone = $companyTimezone ?? config('app.timezone');

    $uiVehicleId = $ui['vehicle_id'] ?? request('vehicle_id');
    $uiStartDate = $ui['start_date'] ?? request('start_date');
    $uiEndDate   = $ui['end_date'] ?? request('end_date');

    $reportType = request('report_type', 'general'); // general | ato | ndis

    $totalTrips = $trips->count();
    $businessTrips = $trips->where('trip_mode', 'business')->count();
    $privateTrips  = $trips->where('trip_mode', 'private')->count();
@endphp

<div class="container">

    {{-- =========================
         PAGE HEADER
    ========================== --}}
    <div class="page-header mb-3">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Compliance & Trip Reports</h1>
                <p class="page-description">
                    Generate ATO-ready, NDIS / Aged Care, and operational trip reports.
                </p>
            </div>

            <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}">
                <input type="hidden" name="export" value="csv">
                <input type="hidden" name="vehicle_id" value="{{ $uiVehicleId }}">
                <input type="hidden" name="start_date" value="{{ $uiStartDate }}">
                <input type="hidden" name="end_date" value="{{ $uiEndDate }}">
                <input type="hidden" name="report_type" value="{{ $reportType }}">

                <button type="submit" class="btn btn-primary">
                    Export Report (CSV)
                </button>
            </form>
        </div>
    </div>

    {{-- =========================
         REPORT TYPE SELECTOR
    ========================== --}}
    <div class="card mb-3">
        <div class="card-body">
            <label class="form-label fw-bold mb-2">Report Type</label>

            <div class="grid grid-3">
                <label class="card p-3 cursor-pointer">
                    <input type="radio" name="report_type" value="general"
                        {{ $reportType === 'general' ? 'checked' : '' }}
                        onchange="this.form.submit()">
                    <strong>General Trip Report</strong>
                    <div class="text-muted small">
                        Operational view of all trips
                    </div>
                </label>

                <label class="card p-3 cursor-pointer">
                    <input type="radio" name="report_type" value="ato"
                        {{ $reportType === 'ato' ? 'checked' : '' }}
                        onchange="this.form.submit()">
                    <strong>ATO Logbook Report</strong>
                    <div class="text-muted small">
                        Business travel aligned to ATO logbook requirements
                    </div>
                </label>

                <label class="card p-3 cursor-pointer">
                    <input type="radio" name="report_type" value="ndis"
                        {{ $reportType === 'ndis' ? 'checked' : '' }}
                        onchange="this.form.submit()">
                    <strong>NDIS / Aged Care Report</strong>
                    <div class="text-muted small">
                        Client-linked travel evidence
                    </div>
                </label>
            </div>
        </div>
    </div>

    {{-- =========================
         FILTERS
    ========================== --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}">
                <input type="hidden" name="report_type" value="{{ $reportType }}">

                <div class="grid grid-3">

                    {{-- WHAT --}}
                    <div>
                        <label class="form-label fw-bold">Vehicle</label>
                        <select name="vehicle_id" class="form-control">
                            <option value="">All Vehicles</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}"
                                    {{ (string)$uiVehicleId === (string)$vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->name }} ({{ $vehicle->registration_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- WHEN --}}
                    <div>
                        <label class="form-label fw-bold">Start Date</label>
                        <input type="date" name="start_date" value="{{ $uiStartDate }}" class="form-control">
                    </div>

                    <div>
                        <label class="form-label fw-bold">End Date</label>
                        <input type="date" name="end_date" value="{{ $uiEndDate }}" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-secondary mt-3">
                    Apply Filters
                </button>

                <div class="mt-2 text-muted small">
                    Times shown in {{ $companyTimezone }}
                </div>
            </form>
        </div>
    </div>

    {{-- =========================
         SUMMARY STRIP
    ========================== --}}
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
                    <strong>{{ $uiStartDate ?: '—' }} → {{ $uiEndDate ?: '—' }}</strong><br>
                    <span class="text-muted small">Period</span>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================
         RESULTS TABLE
    ========================== --}}
    <div class="card">
        <div class="card-body">
            @if($trips->count() === 0)
                <p class="text-muted fst-italic">
                    No trips found for the selected report and filters.
                </p>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Driver</th>
                                <th>Business / Private</th>
                                <th>Customer</th>
                                @if(($purposeOfTravelEnabled ?? false))
                                    <th>Purpose of Travel</th>
                                @endif
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

                                    @if(($purposeOfTravelEnabled ?? false))
                                        <td>{{ $t->purpose_of_travel ?: '—' }}</td>
                                    @endif

                                    <td>
                                        {{ \Carbon\Carbon::parse($t->started_at)->timezone($companyTimezone)->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        {{ $t->end_time
                                            ? \Carbon\Carbon::parse($t->end_time)->timezone($companyTimezone)->format('d/m/Y H:i')
                                            : '—'
                                        }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-2 text-muted small">
                    Distances are shown using each branch’s local measurement unit.
                </div>
            @endif
        </div>
    </div>

</div>

@endsection
