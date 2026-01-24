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
    $companyTimezone     = $companyTimezone ?? config('app.timezone');
    $clientPresenceLabel = trim((string) ($clientPresenceLabel ?? 'Client'));
    $clientPresenceLabel = $clientPresenceLabel !== '' ? $clientPresenceLabel : 'Client';

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
    $dateFormat = str_starts_with($companyTimezone, 'America/')
        ? 'm/d/Y'
        : 'd/m/Y';

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
                    Structured trip reporting for compliance review, internal audits,
                    and operational oversight.
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

    {{-- ================= FILTERS ================= --}}
    <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}"
          class="card sf-report-card mb-3">
        <div class="card-body">

            <div class="grid grid-3 gap-4">

                {{-- Scope --}}
                <div>
                    <h5 class="mb-2">Scope</h5>

                    <div class="form-check">
                        <input class="form-check-input"
                               type="radio"
                               name="scope"
                               value="company"
                               checked>
                        <label class="form-check-label">Company-wide</label>
                    </div>

                    @if($branchesEnabled)
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="radio"
                                   name="scope"
                                   value="branch">
                            <label class="form-check-label">Single branch</label>
                        </div>
                    @endif

                    <div class="text-muted small mt-1">
                        Select whether trips are reported across the entire company
                        or limited to a single branch.
                    </div>
                </div>

                {{-- Filters --}}
                <div>
                    <h5 class="mb-2">Filter by</h5>

                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" name="filter_drivers">
                        <label class="form-check-label">Drivers</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="filter_vehicles">
                        <label class="form-check-label">Vehicles</label>
                    </div>
                </div>

                {{-- Screen columns --}}
                <div>
                    <h5 class="mb-2">Show on screen</h5>

                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" name="show_registration">
                        <label class="form-check-label">Registration number</label>
                    </div>

                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" name="show_purpose">
                        <label class="form-check-label">Purpose of travel</label>
                    </div>

                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" name="show_distance">
                        <label class="form-check-label">Distance</label>
                    </div>

                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" name="show_duration">
                        <label class="form-check-label">Duration</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_times">
                        <label class="form-check-label">Start & end times</label>
                    </div>
                </div>

            </div>

            <div class="flex-between mt-4">
                <div class="text-muted small">
                    Screen options affect on-screen display only.
                    CSV export always includes the full trip dataset.
                </div>

                <button type="submit" class="btn btn-outline-primary">
                    Update Report
                </button>
            </div>

        </div>
    </form>

    {{-- ================= SUMMARY ================= --}}
    <div class="card sf-report-card mb-3">
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
    <div class="card sf-report-card">
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
                                <th>Type</th>
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
                                    <td>
                                        <span class="badge {{ strtolower($t->trip_mode) === 'private' ? 'bg-secondary' : 'bg-success' }}">
                                            {{ strtolower($t->trip_mode) === 'private' ? 'Private' : 'Business' }}
                                        </span>
                                    </td>
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

@push('styles')
<style>
    /* Align with Fleet Manager – Operational */
    .sf-report-card {
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 14px;
        background: #EEF3F8;
        box-shadow: 0 10px 18px rgba(10, 42, 77, 0.16);
    }

    .btn-primary:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }
</style>
@endpush

@endsection
