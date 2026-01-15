@extends('layouts.sharpfleet')

@section('title', 'Compliance & Trip Reports')

@section('sharpfleet-content')

@php
    $companyTimezone = $companyTimezone ?? config('app.timezone');

    $uiVehicleId = $ui['vehicle_id'] ?? request('vehicle_id');
    $uiStartDate = $ui['start_date'] ?? request('start_date');
    $uiEndDate   = $ui['end_date'] ?? request('end_date');

    // general | tax | care
    $reportType = request('report_type', 'general');

    $totalTrips     = $trips->count();
    $businessTrips  = $trips->where('trip_mode', 'business')->count();
    $privateTrips   = $trips->where('trip_mode', 'private')->count();

    $reportLabels = [
        'general' => [
            'title' => 'General Trip Report',
            'badge' => 'GENERAL',
            'desc'  => 'Operational overview of recorded trips',
            'class' => 'badge-neutral',
        ],
        'tax' => [
            'title' => 'Tax Logbook Report',
            'badge' => 'TAX',
            'desc'  => 'Business travel aligned with tax logbook requirements',
            'class' => 'badge-primary',
        ],
        'care' => [
            'title' => 'Client Care Travel Report',
            'badge' => 'CARE',
            'desc'  => 'Client-linked travel evidence for funded care services',
            'class' => 'badge-success',
        ],
    ];

    $activeReport = $reportLabels[$reportType];
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
                    Generate structured reports for tax, care compliance, and internal review.
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
         ACTIVE REPORT STRIP
    ========================== --}}
    <div class="card mb-3">
        <div class="card-body flex-between">
            <div>
                <span class="badge {{ $activeReport['class'] }} me-2">
                    {{ $activeReport['badge'] }}
                </span>
                <strong>{{ $activeReport['title'] }}</strong>
                <div class="text-muted small">
                    {{ $activeReport['desc'] }}
                </div>
            </div>

            <div class="text-muted small text-end">
                Reporting period shown in local time<br>
                Time zone: {{ $companyTimezone }}
            </div>
        </div>
    </div>

    {{-- =========================
         REPORT TYPE SELECTOR
    ========================== --}}
    <div class="card mb-3">
        <div class="card-body">
            <label class="form-label fw-bold mb-2">Report type</label>

            <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}">
                <div class="grid grid-3">

                    {{-- GENERAL --}}
                    <label class="card p-3 cursor-pointer">
                        <input type="radio" name="report_type" value="general"
                            {{ $reportType === 'general' ? 'checked' : '' }}
                            onchange="this.form.submit()">
                        <span class="badge badge-neutral mb-1">GENERAL</span>
                        <strong>General Trip Report</strong>
                        <div class="text-muted small">
                            Internal and operational visibility
                        </div>
                    </label>

                    {{-- TAX --}}
                    <label class="card p-3 cursor-pointer">
                        <input type="radio" name="report_type" value="tax"
                            {{ $reportType === 'tax' ? 'checked' : '' }}
                            onchange="this.form.submit()">
                        <span class="badge badge-primary mb-1">TAX</span>
                        <strong>Tax Logbook Report</strong>
                        <div class="text-muted small">
                            Business travel suitable for tax reporting
                        </div>
                    </label>

                    {{-- CARE --}}
                    <label class="card p-3 cursor-pointer">
                        <input type="radio" name="report_type" value="care"
                            {{ $reportType === 'care' ? 'checked' : '' }}
                            onchange="this.form.submit()">
                        <span class="badge badge-success mb-1">CARE</span>
                        <strong>Client Care Travel Report</strong>
                        <div class="text-muted small">
                            Client-linked travel evidence
                        </div>
                    </label>

                </div>
            </form>
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

                    <div>
                        <label class="form-label fw-bold">Vehicle</label>
                        <select name="vehicle_id" class="form-control">
                            <option value="">All vehicles</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}"
                                    {{ (string)$uiVehicleId === (string)$vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->name }} ({{ $vehicle->registration_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label fw-bold">Start date</label>
                        <input type="date" name="start_date" value="{{ $uiStartDate }}" class="form-control">
                    </div>

                    <div>
                        <label class="form-label fw-bold">End date</label>
                        <input type="date" name="end_date" value="{{ $uiEndDate }}" class="form-control">
                    </div>

                </div>

                <button type="submit" class="btn btn-secondary mt-3">
                    Apply filters
                </button>
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
                    <span class="text-muted small">Reporting period</span>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================
         RESULTS
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
                                    <th>Purpose of travel</th>
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

                                    <td>{{ \Carbon\Carbon::parse($t->started_at)->timezone($companyTimezone)->format('d/m/Y H:i') }}</td>
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
                    Distances and durations are shown using each vehicle’s configured measurement unit (km, mi, or hours).
                </div>
            @endif
        </div>
    </div>

</div>

@endsection
