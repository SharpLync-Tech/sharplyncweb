@php
    use Carbon\Carbon;

    $companyTimezone = $companyTimezone ?? config('app.timezone');
    $dateFormat = str_starts_with($companyTimezone, 'America/')
        ? 'm/d/Y'
        : 'd/m/Y';

    $rangeStart = $ui['start_date'] ?? null;
    $rangeEnd = $ui['end_date'] ?? null;
    $rangeStartLabel = $rangeStart ? Carbon::parse($rangeStart, 'UTC')->timezone($companyTimezone)->format($dateFormat) : '-';
    $rangeEndLabel = $rangeEnd ? Carbon::parse($rangeEnd, 'UTC')->timezone($companyTimezone)->format($dateFormat) : '-';
    $logoPath = public_path('images/sharpfleet/pdf.png');
    $logoData = is_file($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Trips & Compliance Report</title>
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
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="title">Trips & Compliance Report</div>
                    <div class="subtitle">Trip-level compliance view with odometer readings.</div>
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
                <th>Date</th>
                <th>Vehicle</th>
                <th>Registration</th>
                <th>Driver</th>
                <th>{{ $clientPresenceLabel ?? 'Client / Customer' }}</th>
                <th class="text-end">Start odometer</th>
                <th class="text-end">End odometer</th>
                <th class="text-end">Distance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trips as $t)
                @php
                    $unit = isset($t->display_unit)
                        ? (string) $t->display_unit
                        : (((string) ($t->tracking_mode ?? 'distance')) === 'hours' ? 'hours' : 'km');
                    $startReading = $t->display_start ?? $t->start_km ?? null;
                    $endReading = $t->display_end ?? $t->end_km ?? null;
                    $distanceLabel = null;
                    if ($startReading !== null && $endReading !== null && is_numeric($startReading) && is_numeric($endReading)) {
                        $delta = (float) $endReading - (float) $startReading;
                        if ($delta >= 0) {
                            $labelUnit = $unit === 'hours' ? 'h' : $unit;
                            $distanceLabel = number_format($delta, 1) . ' ' . $labelUnit;
                        }
                    }
                @endphp
                <tr>
                    <td>{{ Carbon::parse($t->start_time ?? $t->started_at, 'UTC')->timezone($companyTimezone)->format($dateFormat) }}</td>
                    <td>{{ $t->vehicle_name }}</td>
                    <td>{{ $t->registration_number ?: '-' }}</td>
                    <td>{{ $t->driver_name ?: '-' }}</td>
                    <td>{{ $t->customer_name_display ?: '-' }}</td>
                    <td class="text-end">{{ $startReading !== null && $startReading !== '' ? $startReading . ' ' . ($unit === 'hours' ? 'h' : $unit) : '-' }}</td>
                    <td class="text-end">{{ $endReading !== null && $endReading !== '' ? $endReading . ' ' . ($unit === 'hours' ? 'h' : $unit) : '-' }}</td>
                    <td class="text-end">{{ $distanceLabel ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="muted">No trips found for the selected period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
