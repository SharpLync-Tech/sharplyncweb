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
    | Display toggles (screen only)
    |--------------------------------------------------------------------------
    */
    $showRegistration = request()->boolean('show_registration', true);
    $showPurpose      = request()->boolean('show_purpose', true);
    $showDistance     = request()->boolean('show_distance', true);
    $showDuration     = request()->boolean('show_duration', true);
    $showTimes        = request()->boolean('show_times', false);
    $showDriver       = request()->boolean('show_driver', true);
    $showClient       = request()->boolean('show_client', true);

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
                    Detailed trip-level reporting suitable for tax review, billing support,
                    and internal audit purposes.
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
                        <h5 class="mb-2">Filter trips by</h5>

                        <label class="d-block mb-1">
                            <input type="checkbox" name="filter_vehicle" {{ $uiVehicleId ? 'checked' : '' }}>
                            Vehicle
                        </label>

                        <label class="d-block">
                            <input type="checkbox" name="filter_driver">
                            Driver
                        </label>
                    </div>

                    {{-- Screen columns --}}
                    <div>
                        <h5 class="mb-2">Show on screen</h5>

                        <label class="d-block"><input type="checkbox" name="show_registration" {{ $showRegistration ? 'checked' : '' }}> Registration number</label>
                        <label class="d-block"><input type="checkbox" name="show_purpose" {{ $showPurpose ? 'checked' : '' }}> Purpose of travel</label>
                        <label class="d-block"><input type="checkbox" name="show_distance" {{ $showDistance ? 'checked' : '' }}> Distance</label>
                        <label class="d-block"><input type="checkbox" name="show_duration" {{ $showDuration ? 'checked' : '' }}> Duration</label>
                        <label class="d-block"><input type="checkbox" name="show_times" {{ $showTimes ? 'checked' : '' }}> Start & end times</label>
                        <label class="d-block"><input type="checkbox" name="show_driver" {{ $showDriver ? 'checked' : '' }}> Driver</label>
                        <label class="d-block"><input type="checkbox" name="show_client" {{ $showClient ? 'checked' : '' }}> Client / customer</label>
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

                                @if($showRegistration)
                                    <th>Registration</th>
                                @endif

                                @if($showDriver)
                                    <th>Driver</th>
                                @endif

                                @if($showClient)
                                    <th>Client</th>
                                @endif

                                @if($showPurpose)
                                    <th>Purpose</th>
                                @endif

                                @if($showDistance)
                                    <th class="text-end">Distance</th>
                                @endif

                                @if($showDuration)
                                    <th class="text-end">Duration</th>
                                @endif

                                @if($showTimes)
                                    <th>Start</th>
                                    <th>End</th>
                                @endif
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

                                    @if($showRegistration)
                                        <td>{{ $t->registration_number ?: '—' }}</td>
                                    @endif

                                    @if($showDriver)
                                        <td>{{ $t->driver_name ?: '—' }}</td>
                                    @endif

                                    @if($showClient)
                                        <td>{{ $t->customer_name_display ?: '—' }}</td>
                                    @endif

                                    @if($showPurpose)
                                        <td>{{ $t->purpose_of_travel ?: '—' }}</td>
                                    @endif

                                    @if($showDistance)
                                        <td class="text-end">{{ $t->distance_label ?? '—' }}</td>
                                    @endif

                                    @if($showDuration)
                                        <td class="text-end">{{ $t->duration_label ?? '—' }}</td>
                                    @endif

                                    @if($showTimes)
                                        <td>{{ $t->started_at ? Carbon::parse($t->started_at)->timezone($companyTimezone)->format('H:i') : '—' }}</td>
                                        <td>{{ $t->end_time ? Carbon::parse($t->end_time)->timezone($companyTimezone)->format('H:i') : '—' }}</td>
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

@endsection
