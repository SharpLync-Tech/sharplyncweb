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

<h1>Trip Report – RAW DATA VIEW</h1>
<p><strong>Timezone:</strong> {{ $companyTimezone }}</p>
<p><strong>{{ $clientPresenceLabel }} column label in use</strong></p>

<hr>

@if($trips->count() === 0)
    <p>No trips found.</p>
@else
    <table border="1" cellpadding="6" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Vehicle</th>
                <th>Registration</th>
                <th>Driver</th>
                <th>Type</th>
                <th>{{ $clientPresenceLabel }}</th>
                <th>Start Reading</th>
                <th>End Reading</th>
                <th>Unit</th>
                <th>Total</th>
                <th>Started At</th>
                <th>Ended At</th>
                <th>Duration</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trips as $t)

                @php
                    $startReading = $t->display_start;
                    $endReading   = $t->display_end;
                    $unit         = $t->display_unit ?? 'km';

                    $total = (
                        is_numeric($startReading)
                        && is_numeric($endReading)
                        && $endReading >= $startReading
                    )
                        ? ($endReading - $startReading)
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
                    <td>{{ $t->vehicle_name }}</td>
                    <td>{{ $t->registration_number }}</td>
                    <td>{{ $t->driver_name }}</td>
                    <td>{{ strtolower($t->trip_mode) === 'private' ? 'Private' : 'Business' }}</td>
                    <td>{{ $t->customer_name_display ?: '—' }}</td>
                    <td>{{ is_numeric($startReading) ? $startReading : '—' }}</td>
                    <td>{{ is_numeric($endReading) ? $endReading : '—' }}</td>
                    <td>{{ $unit }}</td>
                    <td>{{ $total !== null ? $total : '—' }}</td>
                    <td>
                        {{ $startedAt
                            ? $startedAt->timezone($companyTimezone)->format($dateFormat)
                            : '—'
                        }}
                    </td>
                    <td>
                        {{ $endedAt
                            ? $endedAt->timezone($companyTimezone)->format($dateFormat)
                            : '—'
                        }}
                    </td>
                    <td>{{ $duration ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@endsection
