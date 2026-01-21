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
                <th>Driver</th>
                <th>Type</th>
                <th>{{ $clientPresenceLabel }}</th>
                <th>Start Reading</th>
                <th>End Reading</th>
                <th>Total</th>
                <th>Unit</th>
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
                @endphp

                <tr>
                    {{-- Vehicle + reg on ONE line --}}
                    <td>
                        {{ $t->vehicle_name }}
                        ({{ $t->registration_number }})
                    </td>

                    {{-- Driver on ONE line --}}
                    <td>{{ $t->driver_name }}</td>

                    {{-- Type --}}
                    <td>{{ strtolower($t->trip_mode) === 'private' ? 'Private' : 'Business' }}</td>

                    {{-- Customer / Client --}}
                    <td>{{ $t->customer_name_display ?: '—' }}</td>

                    {{-- Readings --}}
                    <td>{{ is_numeric($startReading) ? $startReading : '—' }}</td>
                    <td>{{ is_numeric($endReading) ? $endReading : '—' }}</td>

                    {{-- Total --}}
                    <td>{{ $total !== null ? $total : '—' }}</td>

                    {{-- Unit --}}
                    <td>{{ $unit }}</td>

                    {{-- Dates ONE line --}}
                    <td>{{ $startedAt ? $startedAt->format($dateFormat) : '—' }}</td>
                    <td>{{ $endedAt ? $endedAt->format($dateFormat) : '—' }}</td>

                    {{-- Duration --}}
                    <td>{{ $duration ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@endsection
