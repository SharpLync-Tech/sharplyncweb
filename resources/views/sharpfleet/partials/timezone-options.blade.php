@php
    $selectedTimezone = (string) ($selectedTimezone ?? '');

    $allTimezones = \DateTimeZone::listIdentifiers();

    $au = array_values(array_filter($allTimezones, fn ($tz) => strpos($tz, 'Australia/') === 0));
    sort($au);

    $nzCandidates = [
        'Pacific/Auckland',
        'Pacific/Chatham',
        'NZ',
        'NZ-CHAT',
    ];
    $nz = array_values(array_filter($nzCandidates, fn ($tz) => in_array($tz, $allTimezones, true)));

    $usCandidates = [
        'America/New_York',
        'America/Chicago',
        'America/Denver',
        'America/Los_Angeles',
        'America/Phoenix',
        'America/Anchorage',
        'Pacific/Honolulu',
    ];
    $us = array_values(array_filter($usCandidates, fn ($tz) => in_array($tz, $allTimezones, true)));

    $used = array_flip(array_merge($au, $nz, $us));
    $rest = array_values(array_filter($allTimezones, fn ($tz) => !isset($used[$tz])));
    sort($rest);

    $label = function (string $tz): string {
        return str_replace('_', ' ', str_replace('/', ' / ', $tz));
    };
@endphp

<optgroup label="Australia">
    @foreach ($au as $tz)
        <option value="{{ $tz }}" {{ $selectedTimezone === $tz ? 'selected' : '' }}>{{ $label($tz) }}</option>
    @endforeach
</optgroup>

@if (count($nz) > 0)
    <optgroup label="New Zealand">
        @foreach ($nz as $tz)
            <option value="{{ $tz }}" {{ $selectedTimezone === $tz ? 'selected' : '' }}>{{ $label($tz) }}</option>
        @endforeach
    </optgroup>
@endif

@if (count($us) > 0)
    <optgroup label="United States">
        @foreach ($us as $tz)
            <option value="{{ $tz }}" {{ $selectedTimezone === $tz ? 'selected' : '' }}>{{ $label($tz) }}</option>
        @endforeach
    </optgroup>
@endif

<optgroup label="All Other Time Zones">
    @foreach ($rest as $tz)
        <option value="{{ $tz }}" {{ $selectedTimezone === $tz ? 'selected' : '' }}>{{ $label($tz) }}</option>
    @endforeach
</optgroup>
