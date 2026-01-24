@extends('layouts.sharpfleet')

@section('title', 'Compliance & Trip Reports')

@section('sharpfleet-content')

@php
    use Carbon\Carbon;

    /*
    |--------------------------------------------------------------------------
    | Inputs
    |--------------------------------------------------------------------------
    */
    $companyTimezone     = $companyTimezone ?? config('app.timezone');
    $clientPresenceLabel = trim((string) ($clientPresenceLabel ?? 'Client')) ?: 'Client';

    /*
    |--------------------------------------------------------------------------
    | UI column toggles (checkbox driven)
    |--------------------------------------------------------------------------
    */
    $showVehicles     = request()->boolean('filter_vehicles', true);
    $showDrivers      = request()->boolean('filter_drivers', true);

    $showRegistration = request()->boolean('show_registration');
    $showPurpose      = request()->boolean('show_purpose');
    $showDistance     = request()->boolean('show_distance');
    $showDuration     = request()->boolean('show_duration');
    $showTimes        = request()->boolean('show_times');

    /*
    |--------------------------------------------------------------------------
    | Dates
    |--------------------------------------------------------------------------
    */
    $dateFormat = str_starts_with($companyTimezone, 'America/') ? 'm/d/Y' : 'd/m/Y';

    /*
    |--------------------------------------------------------------------------
    | Totals
    |--------------------------------------------------------------------------
    */
    $totalTrips    = $trips->count();
    $businessTrips = $trips->where('trip_mode', 'business')->count();
    $privateTrips  = $trips->where('trip_mode', 'private')->count();
@endphp

<div class="container">

    {{-- ================= HEADER ================= --}}
    <div class="page-header mb-3">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Compliance & Trip Reports</h1>
                <p class="page-description">
                    Structured trip reporting for compliance review, audits, and internal oversight.
                </p>
            </div>

            <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}">
                @foreach(request()->except('export') as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <input type="hidden" name="export" value="csv">
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

                    <label class="form-check">
                        <input class="form-check-input" type="radio" name="scope" value="company" checked>
                        Company-wide
                    </label>

                    <label class="form-check">
                        <input class="form-check-input" type="radio" name="scope" value="branch">
                        Single branch
                    </label>

                    <div class="text-muted small mt-1">
                        Select whether trips are reported across the entire company or a single branch.
                    </div>
                </div>

                {{-- Filter by --}}
                <div>
                    <h5 class="mb-2">Filter by</h5>

                    <label class="form-check">
                        <input class="form-check-input" type="checkbox" name="filter_drivers" {{ $showDrivers ? 'checked' : '' }}>
                        Drivers
                    </label>

                    <label class="form-check">
                        <input class="form-check-input" type="checkbox" name="filter_vehicles" {{ $showVehicles ? 'checked' : '' }}>
                        Vehicles
                    </label>
                </div>

                {{-- Show columns --}}
                <div>
                    <h5 class="mb-2">Show on screen</h5>

                    <label class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_registration" {{ $showRegistration ? 'checked' : '' }}>
                        Registration number
                    </label>

                    <label class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_purpose" {{ $showPurpose ? 'checked' : '' }}>
                        Purpose of travel
                    </label>

                    <label class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_distance" {{ $showDistance ? 'checked' : '' }}>
                        Distance
                    </label>

                    <label class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_duration" {{ $showDuration ? 'checked' : '' }}>
                        Duration
                    </label>

                    <label class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_times" {{ $showTimes ? 'checked' : '' }}>
                        Start & end times
                    </label>
                </div>

            </div>

            <div class="flex-between mt-4">
                <div class="text-muted small">
                    Screen options affect on-screen display only.
                    CSV export always includes the full trip dataset.
                </div>

                <button type="submit" class="btn-sf-navy">
                    Update Report
                </button>
            </div>

        </div>
    </form>

    {{-- ================= RESULTS ================= --}}
    <div class="card sf-report-card">
        <div class="card-body">

            @if($trips->isEmpty())
                <p class="text-muted fst-italic">
                    No trips found for the selected filters.
                </p>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            @if($showVehicles)
                                <th>Vehicle</th>
                            @endif

                            @if($showDrivers)
                                <th>Driver</th>
                            @endif

                            <th>Type</th>

                            <th>{{ $clientPresenceLabel }}</th>

                            @if($showDistance)
                                <th class="text-end">Distance</th>
                            @endif

                            @if($showDuration)
                                <th class="text-end">Duration</th>
                            @endif

                            @if($showTimes)
                                <th>Started</th>
                                <th>Ended</th>
                            @endif
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($trips as $t)
                            <tr>
                                @if($showVehicles)
                                    <td class="fw-bold">
                                        {{ $t->vehicle_name }}
                                        @if($showRegistration)
                                            <br><small class="text-muted">{{ $t->registration_number }}</small>
                                        @endif
                                    </td>
                                @endif

                                @if($showDrivers)
                                    <td>{{ $t->driver_name }}</td>
                                @endif

                                <td>
                                    <span class="badge {{ strtolower($t->trip_mode) === 'private' ? 'bg-secondary' : 'bg-success' }}">
                                        {{ strtolower($t->trip_mode) === 'private' ? 'Private' : 'Business' }}
                                    </span>
                                </td>

                                <td>{{ $t->customer_name_display ?: '—' }}</td>

                                @if($showDistance)
                                    <td class="text-end">{{ $t->distance_label ?? '—' }}</td>
                                @endif

                                @if($showDuration)
                                    <td class="text-end">{{ $t->duration_label ?? '—' }}</td>
                                @endif

                                @if($showTimes)
                                    <td>{{ Carbon::parse($t->started_at)->timezone($companyTimezone)->format($dateFormat) }}</td>
                                    <td>
                                        {{ $t->end_time
                                            ? Carbon::parse($t->end_time)->timezone($companyTimezone)->format($dateFormat)
                                            : '—'
                                        }}
                                    </td>
                                @endif
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
    .sf-report-card {
        border: 1px solid rgba(255,255,255,0.25);
        border-radius: 14px;
        background: #EEF3F8;
        box-shadow: 0 10px 18px rgba(10,42,77,0.16);
    }
</style>
@endpush

@endsection
