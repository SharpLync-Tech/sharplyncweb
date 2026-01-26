@php
    use Carbon\Carbon;

    $companyTimezone = $companyTimezone ?? config('app.timezone');
    $dateFormat = str_starts_with($companyTimezone, 'America/')
        ? 'm/d/Y'
        : 'd/m/Y';

    $timeFormat = 'H:i';

    $rangeStart = $ui['start_date'] ?? null;
    $rangeEnd = $ui['end_date'] ?? null;
    $rangeStartLabel = $rangeStart ? Carbon::parse($rangeStart, 'UTC')->timezone($companyTimezone)->format($dateFormat) : '-';
    $rangeEndLabel = $rangeEnd ? Carbon::parse($rangeEnd, 'UTC')->timezone($companyTimezone)->format($dateFormat) : '-';
    $logoPath = public_path('images/sharpfleet/pdf.png');
    $logoData = is_file($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

    $clientLabel = $clientPresenceLabel ?? 'Client / Customer';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Client Transport Report</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #0A2A4D;
        }
        .header {
            margin-bottom: 12px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .subtitle {
            font-size: 11px;
            color: #4b5b6b;
        }
        .meta {
            margin-top: 6px;
            font-size: 11px;
            color: #4b5b6b;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .logo img {
            height: 40px;
            width: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #d9e1ea;
            padding: 6px 8px;
            vertical-align: top;
        }
        th {
            background: #eef3f8;
            font-weight: bold;
            text-align: left;
        }
        .text-end {
            text-align: right;
        }
        .muted {
            color: #4b5b6b;
        }
        .footer {
            margin-top: 16px;
            font-size: 10px;
            color: #4b5b6b;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="title">Client Transport Report</div>
                    <div class="subtitle">Client trip activity with timing, vehicle, and driver details.</div>
                    <div class="meta">
                        Reporting period: {{ $rangeStartLabel }} - {{ $rangeEndLabel }}
                    </div>
                </td>
                <td style="text-align:right;">
                    @if($logoData)
                        <div class="logo">
                            <img src="data:image/png;base64,{{ $logoData }}" alt="SharpFleet">
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ $clientLabel }}</th>
                <th>Date/Time</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Vehicle</th>
                <th>Driver</th>
                <th>Trip Purpose</th>
                <th class="text-end">Distance (km)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trips as $t)
                @php
                    $start = $t->started_at ?? null;
                    $endValue = $t->end_time ?? $t->ended_at ?? null;

                    $dateTimeLabel = $start
                        ? Carbon::parse($start, 'UTC')->timezone($companyTimezone)->format($dateFormat . ' H:i')
                        : '-';
                    $startTimeLabel = $start
                        ? Carbon::parse($start, 'UTC')->timezone($companyTimezone)->format($timeFormat)
                        : '-';
                    $endTimeLabel = $endValue
                        ? Carbon::parse($endValue, 'UTC')->timezone($companyTimezone)->format($timeFormat)
                        : '-';

                    $distanceLabel = '-';
                    if (isset($t->start_km, $t->end_km) && is_numeric($t->start_km) && is_numeric($t->end_km)) {
                        $delta = (float) $t->end_km - (float) $t->start_km;
                        if ($delta >= 0) {
                            $distanceLabel = number_format($delta, 1);
                        }
                    }

                    $tripPurpose = '';
                    if ($purposeOfTravelEnabled ?? false) {
                        $rawMode = strtolower((string) ($t->trip_mode ?? ''));
                        $isBusiness = $rawMode !== 'private';
                        $tripPurpose = $isBusiness ? ($t->purpose_of_travel ?? '') : '';
                    }
                @endphp
                <tr>
                    <td>{{ $t->customer_name_display ?: '-' }}</td>
                    <td>{{ $dateTimeLabel }}</td>
                    <td>{{ $startTimeLabel }}</td>
                    <td>{{ $endTimeLabel }}</td>
                    <td>{{ $t->vehicle_name }}</td>
                    <td>{{ $t->driver_name }}</td>
                    <td>{{ $tripPurpose }}</td>
                    <td class="text-end">{{ $distanceLabel }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="muted">No trips found for the selected period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        This report is system-generated and reflects recorded trip data at the time of export.
    </div>
</body>
</html>
