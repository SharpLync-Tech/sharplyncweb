@extends('layouts.sharpfleet')

@section('title', 'Trip Report – Test Layout')

@section('sharpfleet-content')

<link rel="stylesheet" href="{{ asset('css/sharpfleet/sharpfleet-reports.css') }}">

@php
    use Carbon\Carbon;

    /*
    |--------------------------------------------------------------------------
    | Inputs resolved by controller
    |--------------------------------------------------------------------------
    */
    $companyTimezone     = $companyTimezone ?? config('app.timezone');
    $clientPresenceLabel = trim((string) ($clientPresenceLabel ?? 'Client'));
    $clientPresenceLabel = $clientPresenceLabel !== '' ? $clientPresenceLabel : 'Client';

    /*
    |--------------------------------------------------------------------------
    | Date format
    |--------------------------------------------------------------------------
    */
    $dateFormat = str_starts_with($companyTimezone, 'America/')
        ? 'm/d/Y H:i'
        : 'd/m/Y H:i';
@endphp

<div class="sf-report-wrapper">

    {{-- ================= HEADER ================= --}}
    <div class="page-header mb-4">
        <div>
            <h1 class="page-title">Trip Report (Test)</h1>
            <p class="page-description">
                Desktop-first test layout for detailed trip reporting.
            </p>
        </div>
    </div>

    {{-- ================= RESULTS ================= --}}
    <div class="sf-report-surface">

        @if($trips->count() === 0)
            <p class="text-muted fst-italic">No trips available.</p>
        @else
            <div class="sf-report-scroll">
                <table class="sf-report-table table table-sm align-middle">
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
                            <th class="text-end">Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trips as $t)

                            @php
                                $startReading = $t->display_start ?? null;
                                $endReading   = $t->display_end ?? null;
                                $unit         = $t->display_unit ?? 'km';

                                $distanceTotal = (
                                    is_numeric($startReading)
                                    && is_numeric($endReading)
                                    && $endReading >= $startReading
                                )
                                    ? number_format($endReading - $startReading, 2)
                                    : null;

                                $startedAt = $t->started_at
                                    ? Carbon::parse($t->started_at)
                                    : null;

                                $endedAt = $t->end_time
                                    ? Carbon::parse($t->end_time)
                                    : null;

                                $duration = (
                                    $startedAt && $endedAt
                                )
                                    ? $startedAt->diff($endedAt)->format('%h:%I')
                                    : null;
                            @endphp

                            <tr>
                                {{-- Vehicle --}}
                                <td>
                                    <div class="sf-report-vehicle">{{ $t->vehicle_name }}</div>
                                    <div class="sf-report-sub">{{ $t->registration_number }}</div>
                                </td>

                                {{-- Driver --}}
                                <td>{{ $t->driver_name }}</td>

                                {{-- Type --}}
                                <td class="sf-trip-type">
                                    {{ strtolower($t->trip_mode) === 'private' ? 'Private' : 'Business' }}
                                </td>

                                {{-- Customer / Client --}}
                                <td>{{ $t->customer_name_display ?: '—' }}</td>

                                {{-- Start --}}
                                <td class="text-end">
                                    {{ is_numeric($startReading) ? $startReading : '—' }}
                                    @if(is_numeric($startReading))
                                        <span class="sf-report-unit">{{ $unit }}</span>
                                    @endif
                                </td>

                                {{-- End --}}
                                <td class="text-end">
                                    {{ is_numeric($endReading) ? $endReading : '—' }}
                                    @if(is_numeric($endReading))
                                        <span class="sf-report-unit">{{ $unit }}</span>
                                    @endif
                                </td>

                                {{-- Total --}}
                                <td class="text-end sf-report-total">
                                    {{ $distanceTotal ?? '—' }}
                                    @if($distanceTotal !== null)
                                        <span class="sf-report-unit">{{ $unit }}</span>
                                    @endif
                                </td>

                                {{-- Started --}}
                                <td>
                                    {{ $startedAt
                                        ? $startedAt->timezone($companyTimezone)->format($dateFormat)
                                        : '—'
                                    }}
                                </td>

                                {{-- Ended --}}
                                <td>
                                    {{ $endedAt
                                        ? $endedAt->timezone($companyTimezone)->format($dateFormat)
                                        : '—'
                                    }}
                                </td>

                                {{-- Duration --}}
                                <td class="text-end sf-report-duration">
                                    {{ $duration ? $duration . ' hrs' : '—' }}
                                </td>
                            </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3 text-muted small">
                Distance units (km / mi / hours) are resolved per vehicle or branch.
            </div>
        @endif

    </div>

</div>

@endsection
