@extends('layouts.sharpfleet')

@section('title', 'Trip Report – Test Layout')

@section('sharpfleet-content')

@php
    use Carbon\Carbon;

    $companyTimezone     = $companyTimezone ?? config('app.timezone');
    $clientPresenceLabel = trim((string) ($clientPresenceLabel ?? 'Client'));
    $clientPresenceLabel = $clientPresenceLabel !== '' ? $clientPresenceLabel : 'Client';

    $dateFormat = str_starts_with($companyTimezone, 'America/')
        ? 'm/d/Y H:i'
        : 'd/m/Y H:i';
@endphp

{{-- ✅ Explicitly load reports stylesheet --}}
<link rel="stylesheet" href="{{ asset('css/sharpfleet/sharpfleet-reports.css') }}">

{{-- ❌ NO BOOTSTRAP CONTAINER --}}
<div class="sf-report-wrapper">

    <div class="page-header mb-4">
        <h1 class="page-title">Trip Report (Test)</h1>
        <p class="page-description">
            Desktop-first test layout for detailed trip reporting.
        </p>
    </div>

    <div class="sf-report-surface">

        @if($trips->count() === 0)
            <p class="text-muted fst-italic">
                No trips available.
            </p>
        @else
            <div class="sf-report-scroll">
                <table class="sf-report-table">
                    <thead>
                        <tr>
                            <th class="sf-col-vehicle">Vehicle</th>
                            <th class="sf-col-driver">Driver</th>
                            <th class="sf-col-type">Type</th>
                            <th class="sf-col-customer">{{ $clientPresenceLabel }}</th>
                            <th class="sf-col-start">Start</th>
                            <th class="sf-col-end">End</th>
                            <th class="sf-col-total">Total</th>
                            <th class="sf-col-started">Started</th>
                            <th class="sf-col-ended">Ended</th>
                            <th class="sf-col-duration">Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trips as $t)
                            @php
                                $startReading = $t->display_start;
                                $endReading   = $t->display_end;
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

                                $duration = ($startedAt && $endedAt)
                                    ? $startedAt->diffForHumans($endedAt, [
                                        'parts' => 2,
                                        'short' => true,
                                        'syntax' => Carbon::DIFF_ABSOLUTE,
                                    ])
                                    : null;
                            @endphp

                            <tr>
                                <td class="sf-col-vehicle">
                                    <span class="sf-report-vehicle">{{ $t->vehicle_name }}</span>
                                    <div class="sf-report-sub">{{ $t->registration_number }}</div>
                                </td>

                                <td class="sf-col-driver">{{ $t->driver_name }}</td>

                                <td class="sf-col-type sf-trip-type">
                                    {{ strtolower($t->trip_mode) === 'private' ? 'Private' : 'Business' }}
                                </td>

                                <td class="sf-col-customer">
                                    {{ $t->customer_name_display ?: '—' }}
                                </td>

                                <td class="sf-col-start text-end">
                                    {{ is_numeric($startReading) ? $startReading : '—' }}
                                    <span class="sf-report-unit">{{ is_numeric($startReading) ? $unit : '' }}</span>
                                </td>

                                <td class="sf-col-end text-end">
                                    {{ is_numeric($endReading) ? $endReading : '—' }}
                                    <span class="sf-report-unit">{{ is_numeric($endReading) ? $unit : '' }}</span>
                                </td>

                                <td class="sf-col-total text-end sf-report-total">
                                    {{ $distanceTotal ?? '—' }}
                                    <span class="sf-report-unit">{{ $distanceTotal ? $unit : '' }}</span>
                                </td>

                                <td class="sf-col-started">
                                    {{ $startedAt ? $startedAt->timezone($companyTimezone)->format($dateFormat) : '—' }}
                                </td>

                                <td class="sf-col-ended">
                                    {{ $endedAt ? $endedAt->timezone($companyTimezone)->format($dateFormat) : '—' }}
                                </td>

                                <td class="sf-col-duration text-end sf-report-duration">
                                    {{ $duration ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3 text-muted small">
                Distances and durations respect vehicle and branch units (km, mi, hours).
            </div>
        @endif

    </div>

</div>

@endsection
