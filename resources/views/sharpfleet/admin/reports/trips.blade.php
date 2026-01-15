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

    $reportType = request('report_type', 'general');

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

    /*
    |--------------------------------------------------------------------------
    | Report labels
    |--------------------------------------------------------------------------
    */
    $reportLabels = [
        'general' => [
            'title' => 'Trip Report',
            'badge' => 'GENERAL',
            'desc'  => 'Internal and operational visibility',
            'class' => 'badge-neutral',
        ],
        'tax' => [
            'title' => 'Logbook Report',
            'badge' => 'TAX',
            'desc'  => 'Business travel suitable for tax reporting',
            'class' => 'badge-primary',
        ],
        'care' => [
            'title' => $clientPresenceLabel . ' Care Travel Report',
            'badge' => 'CARE',
            'desc'  => $clientPresenceLabel . '-linked travel evidence',
            'class' => 'badge-success',
        ],
    ];

    $activeReport = $reportLabels[$reportType] ?? $reportLabels['general'];
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
                <input type="hidden" name="report_type" value="{{ $reportType }}">
                <button type="submit" class="btn btn-primary">
                    Export Report (CSV)
                </button>
            </form>
        </div>
    </div>

    {{-- ================= ACTIVE REPORT ================= --}}
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
                                @if($purposeOfTravelEnabled)
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

                                    @if($purposeOfTravelEnabled)
                                        <td>{{ $t->purpose_of_travel ?: '—' }}</td>
                                    @endif

                                    <td>{{ Carbon::parse($t->started_at)->timezone($companyTimezone)->format($dateFormat) }}</td>
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

                <div class="mt-2 text-muted small">
                    Distances and durations are shown using each vehicle’s configured unit (km, mi, or hours).
                </div>
            @endif
        </div>
    </div>

</div>

@endsection
