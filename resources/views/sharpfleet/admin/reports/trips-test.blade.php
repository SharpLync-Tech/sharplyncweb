@extends('layouts.sharpfleet')

@section('title', 'Trip Report – RAW')

@section('sharpfleet-content')

@php
    use Carbon\Carbon;

    $companyTimezone     = $companyTimezone ?? config('app.timezone');
    $clientPresenceLabel = trim((string) ($clientPresenceLabel ?? 'Client'));
    $clientPresenceLabel = $clientPresenceLabel !== '' ? $clientPresenceLabel : 'Client';

    $dateFormat = str_starts_with($companyTimezone, 'America/')
        ? 'm/d/Y H:i'
        : 'd/m/Y H:i';

    $formatTotal = function ($val) {
        if ($val === null || $val === '') return '';
        if (!is_numeric($val)) return '';
        $num = (float) $val;
        // If it's effectively an integer, show no decimals; else show 2
        return (abs($num - round($num)) < 0.00001) ? (string) (int) round($num) : number_format($num, 2, '.', '');
    };

    $formatDuration = function (?string $startedAt, ?string $endedAt) use ($companyTimezone) {
        if (!$startedAt || !$endedAt) return '';

        try {
            $s = Carbon::parse($startedAt, $companyTimezone);
            $e = Carbon::parse($endedAt, $companyTimezone);
        } catch (\Throwable $e) {
            return '';
        }

        $seconds = $s->diffInSeconds($e, false);
        if ($seconds <= 0) return '';

        $hours = intdiv($seconds, 3600);
        $seconds = $seconds % 3600;

        $mins = intdiv($seconds, 60);
        $secs = $seconds % 60;

        $parts = [];
        if ($hours > 0) $parts[] = $hours . 'h';
        if ($mins > 0) $parts[] = $mins . 'm';
        if ($hours === 0 && $secs > 0) $parts[] = $secs . 's'; // only show seconds when < 1h

        return implode(' ', $parts);
    };
@endphp

<h1>Trip Report – RAW DATA VIEW</h1>
<p><strong>Timezone:</strong> {{ $companyTimezone }}</p>
<p><strong>{{ $clientPresenceLabel }} column label in use</strong></p>

@if($trips->count() === 0)
    <p>No trips available.</p>
@else
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>Vehicle</th>
                <th>Registration</th>
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
                    $startReading = $t->display_start ?? $t->start_km ?? null;
                    $endReading   = $t->display_end ?? $t->end_km ?? null;
                    $unit         = $t->display_unit ?? ((($t->tracking_mode ?? 'distance') === 'hours') ? 'hours' : 'km');

                    $total = (
                        is_numeric($startReading)
                        && is_numeric($endReading)
                        && (float)$endReading >= (float)$startReading
                    )
                        ? ((float)$endReading - (float)$startReading)
                        : null;

                    $startedAtRaw = $t->started_at ?? null;
                    $endedAtRaw   = $t->end_time ?? $t->ended_at ?? null;

                    $startedAtText = $startedAtRaw
                        ? Carbon::parse($startedAtRaw)->timezone($companyTimezone)->format($dateFormat)
                        : '';

                    $endedAtText = $endedAtRaw
                        ? Carbon::parse($endedAtRaw)->timezone($companyTimezone)->format($dateFormat)
                        : '';

                    $durationText = $formatDuration($startedAtRaw, $endedAtRaw);
                @endphp

                <tr>
                    <td>{{ $t->vehicle_name ?? '' }}</td>
                    <td>{{ $t->registration_number ?? '' }}</td>
                    <td>{{ $t->driver_name ?? '' }}</td>
                    <td>{{ strtolower((string)($t->trip_mode ?? '')) === 'private' ? 'Private' : 'Business' }}</td>
                    <td>{{ $t->customer_name_display ?? $t->customer_name ?? '' }}</td>

                    <td>{{ is_numeric($startReading) ? $formatTotal($startReading) : '' }}</td>
                    <td>{{ is_numeric($endReading) ? $formatTotal($endReading) : '' }}</td>
                    <td>{{ $total !== null ? $formatTotal($total) : '' }}</td>
                    <td>{{ $unit }}</td>

                    <td>{{ $startedAtText }}</td>
                    <td>{{ $endedAtText }}</td>
                    <td>{{ $durationText }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@endsection
