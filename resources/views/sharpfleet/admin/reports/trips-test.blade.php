@extends('layouts.sharpfleet')

@section('title', 'Trip Report – Raw Test')

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

{{-- RAW VIEW: minimal CSS, only to force single-line cells --}}
<style>
    .sf-raw-wrap {
        padding: 18px;
    }

    .sf-raw-table-wrap {
        overflow-x: auto;
        border: 1px solid #999;
        background: #fff;
    }

    table.sf-raw-table {
        border-collapse: collapse;
        width: max-content;     /* key: table uses natural width */
        min-width: 100%;        /* but never smaller than container */
    }

    table.sf-raw-table th,
    table.sf-raw-table td {
        border: 1px solid #999;
        padding: 8px 10px;
        white-space: nowrap;    /* key: prevent wrapping everywhere */
        vertical-align: top;
        font-family: inherit;
        font-size: 14px;
    }

    table.sf-raw-table th {
        font-weight: 700;
        background: #f2f2f2;
    }

    .sf-raw-right { text-align: right; }
    .sf-raw-center { text-align: center; }
</style>

<div class="sf-raw-wrap">
    <h1>Trip Report – RAW DATA VIEW</h1>

    <p><strong>Timezone:</strong> {{ $companyTimezone }}</p>
    <p><strong>{{ $clientPresenceLabel }} column label in use</strong></p>

    <hr>

    @if($trips->count() === 0)
        <p>No trips found.</p>
    @else
        <div class="sf-raw-table-wrap">
            <table class="sf-raw-table">
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Registration</th>
                        <th>Driver</th>
                        <th>Type</th>
                        <th>{{ $clientPresenceLabel }}</th>
                        <th class="sf-raw-right">Start Reading</th>
                        <th class="sf-raw-right">End Reading</th>
                        <th class="sf-raw-right">Total</th>
                        <th class="sf-raw-center">Unit</th>
                        <th>Started At</th>
                        <th>Ended At</th>
                        <th class="sf-raw-right">Duration</th>
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
                                ? ($endReading - $startReading)
                                : null;

                            $startedAt = $t->started_at
                                ? Carbon::parse($t->started_at)->timezone($companyTimezone)
                                : null;

                            $endedAt = $t->end_time
                                ? Carbon::parse($t->end_time)->timezone($companyTimezone)
                                : null;

                            $duration = ($startedAt && $endedAt)
                                ? $startedAt->diffForHumans($endedAt, [
                                    'parts' => 2,
                                    'short' => true,
                                    'syntax' => Carbon::DIFF_ABSOLUTE,
                                ])
                                : null;

                            // Force single-line date/time even if the browser *really* wants to wrap
                            $startedText = $startedAt ? str_replace(' ', "\u{00A0}", $startedAt->format($dateFormat)) : '—';
                            $endedText   = $endedAt ? str_replace(' ', "\u{00A0}", $endedAt->format($dateFormat)) : '—';

                            // Force single-line names (Jannie Brits)
                            $driverText = trim((string) ($t->driver_name ?? ''));
                            $driverText = $driverText !== '' ? str_replace(' ', "\u{00A0}", $driverText) : '—';

                            $vehicleText = trim((string) ($t->vehicle_name ?? ''));
                            $regoText = trim((string) ($t->registration_number ?? ''));
                        @endphp

                        <tr>
                            <td>{{ $vehicleText !== '' ? $vehicleText : '—' }}</td>
                            <td>{{ $regoText !== '' ? $regoText : '—' }}</td>
                            <td>{!! e($driverText) !!}</td>
                            <td>{{ strtolower((string) ($t->trip_mode ?? '')) === 'private' ? 'Private' : 'Business' }}</td>
                            <td>{{ $t->customer_name_display ?: '—' }}</td>

                            <td class="sf-raw-right">{{ is_numeric($startReading) ? $startReading : '—' }}</td>
                            <td class="sf-raw-right">{{ is_numeric($endReading) ? $endReading : '—' }}</td>

                            <td class="sf-raw-right">{{ $total !== null ? $total : '—' }}</td>
                            <td class="sf-raw-center">{{ $unit }}</td>

                            <td>{!! e($startedText) !!}</td>
                            <td>{!! e($endedText) !!}</td>

                            <td class="sf-raw-right">{{ $duration ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
