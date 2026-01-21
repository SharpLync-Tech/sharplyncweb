@extends('layouts.sharpfleet-reports')

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

    $formatDuration = function (?string $startedAt, ?string $endedAt) use ($companyTimezone) {
        if (!$startedAt || !$endedAt) return '';
        $s = Carbon::parse($startedAt, $companyTimezone);
        $e = Carbon::parse($endedAt, $companyTimezone);
        $seconds = $s->diffInSeconds($e);
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;
        return ($h ? "{$h}h " : '') . ($m ? "{$m}m " : '') . ($h === 0 && $s ? "{$s}s" : '');
    };
@endphp

<h1>Trip Report – RAW DATA VIEW</h1>
<p><strong>Timezone:</strong> {{ $companyTimezone }}</p>
<p><strong>{{ $clientPresenceLabel }} column label in use</strong></p>

<div align="left">
<table border="1" cellpadding="6" cellspacing="0" width="2000">
    <thead>
        <tr>
            <th nowrap>Vehicle</th>
            <th nowrap>Registration</th>
            <th nowrap>Driver</th>
            <th nowrap>Type</th>
            <th nowrap>{{ $clientPresenceLabel }}</th>
            <th nowrap>Start Reading</th>
            <th nowrap>End Reading</th>
            <th nowrap>Total</th>
            <th nowrap>Unit</th>
            <th nowrap>Started At</th>
            <th nowrap>Ended At</th>
            <th nowrap>Duration</th>
        </tr>
    </thead>
    <tbody>
        @foreach($trips as $t)
            @php
                $start = $t->display_start;
                $end   = $t->display_end;
                $unit  = $t->display_unit ?? 'km';
                $total = (is_numeric($start) && is_numeric($end)) ? ($end - $start) : null;
                $startedAt = $t->started_at;
                $endedAt   = $t->end_time;
            @endphp
            <tr>
                <td nowrap>{{ $t->vehicle_name }}</td>
                <td nowrap>{{ $t->registration_number }}</td>
                <td nowrap>{{ $t->driver_name }}</td>
                <td nowrap>{{ strtolower($t->trip_mode) === 'private' ? 'Private' : 'Business' }}</td>
                <td nowrap>{{ $t->customer_name_display ?? '' }}</td>
                <td nowrap>{{ $start }}</td>
                <td nowrap>{{ $end }}</td>
                <td nowrap>{{ $total }}</td>
                <td nowrap>{{ $unit }}</td>
                <td nowrap>{{ $startedAt ? Carbon::parse($startedAt)->timezone($companyTimezone)->format($dateFormat) : '' }}</td>
                <td nowrap>{{ $endedAt ? Carbon::parse($endedAt)->timezone($companyTimezone)->format($dateFormat) : '' }}</td>
                <td nowrap>{{ $formatDuration($startedAt, $endedAt) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>
@endsection
