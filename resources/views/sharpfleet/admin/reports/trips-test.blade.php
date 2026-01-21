@extends('layouts.sharpfleet')

@section('title', 'Trip Report – Test Layout')

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
    | Date formatting (display only)
    |--------------------------------------------------------------------------
    */
    if (str_starts_with($companyTimezone, 'America/')) {
        $dateFormat = 'm/d/Y H:i';
    } else {
        $dateFormat = 'd/m/Y H:i';
    }
@endphp

{{-- TRUE FULL-WIDTH REPORT --}}
<div class="sf-report-wrapper">

    {{-- ================= HEADER ================= --}}
    <div class="page-header mb-3">
        <div>
            <h1 class="page-title">Trip Report (Test)</h1>
            <p class="page-description">
                Desktop-first test layout for detailed trip reporting.
            </p>
        </div>
    </div>

    {{-- ================= RESULTS ================= --}}
    <div class="card">
        <div class="card-body">

            @if($trips->count() === 0)
                <p class="text-muted fst-italic">
                    No trips available.
                </p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle sf-report-table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Driver</th>
                                <th>Type</th>
                                <th>{{ $clientPresenceLabel }}</th>
                                <th class="text-end">Start</th>
                                <th class="text-end">End</th>
                                <th class="text-end">Total</th>
                                <th>Started</th>
                                <th>Ended</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trips as $t)

                                @php
                                    $startReading = $t->display_start ?? null;
                                    $endReading   = $t->display_end ?? null;
                                    $unit         = $t->display_unit ?? 'km';

                                    $total = (
                                        is_numeric($startReading)
                                        && is_numeric($endReading)
                                        && $endReading >= $startReading
                                    )
                                        ? number_format($endReading - $startReading, 2)
                                        : null;
                                @endphp

                                <tr>
                                    {{-- Vehicle --}}
                                    <td class="fw-semibold">
                                        {{ $t->vehicle_name }}
                                        <span class="text-muted small ms-1">
                                            ({{ $t->registration_number }})
                                        </span>
                                    </td>

                                    {{-- Driver --}}
                                    <td>{{ $t->driver_name }}</td>

                                    {{-- Type --}}
                                    <td>
                                        {{ strtolower($t->trip_mode) === 'private' ? 'Private' : 'Business' }}
                                    </td>

                                    {{-- Customer / Client --}}
                                    <td>
                                        {{ $t->customer_name_display ?: '—' }}
                                    </td>

                                    {{-- Start reading --}}
                                    <td class="text-end">
                                        {{ is_numeric($startReading) ? $startReading . ' ' . $unit : '—' }}
                                    </td>

                                    {{-- End reading --}}
                                    <td class="text-end">
                                        {{ is_numeric($endReading) ? $endReading . ' ' . $unit : '—' }}
                                    </td>

                                    {{-- Total --}}
                                    <td class="text-end fw-semibold">
                                        {{ $total !== null ? $total . ' ' . $unit : '—' }}
                                    </td>

                                    {{-- Started --}}
                                    <td>
                                        {{ $t->started_at
                                            ? Carbon::parse($t->started_at)->timezone($companyTimezone)->format($dateFormat)
                                            : '—'
                                        }}
                                    </td>

                                    {{-- Ended --}}
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
                    Distances and totals respect vehicle / branch units (km, mi, or hours).
                </div>
            @endif

        </div>
    </div>

</div>

{{-- PAGE-LOCAL STYLES (TEST ONLY) --}}
<style>
    .sf-report-wrapper {
        max-width: 2000px;
        margin: 0 auto;
        padding: 0 2rem 2rem;
    }

    .sf-report-table {
        white-space: nowrap;
    }

    .sf-report-table td,
    .sf-report-table th {
        vertical-align: middle;
    }
</style>

@endsection
