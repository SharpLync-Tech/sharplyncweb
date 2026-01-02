@extends('layouts.sharpfleet')

@section('title', 'Reminders')

@section('sharpfleet-content')

<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Reminders</h1>
                <p class="page-description">These are the current registration and servicing reminders for your organisation.</p>
            </div>
            <a href="/app/sharpfleet/admin/vehicles" class="btn btn-secondary">View Vehicles</a>
        </div>
    </div>

    @php
        $settings = $digest['settings'] ?? [];
        $formatDate = function ($value) use ($dateFormat) {
            try {
                if ($value instanceof \Carbon\Carbon) {
                    return $value->format($dateFormat);
                }
                return \Carbon\Carbon::parse((string) $value)->format($dateFormat);
            } catch (\Throwable $e) {
                return '—';
            }
        };
    @endphp

    <div class="card mb-4">
        <div class="card-body">
            <div class="mb-2">
                <strong>Email recipient:</strong>
                {{ $recipient ?: 'Not set' }}
            </div>
            <div class="text-muted">
                Timezone: {{ $timezone }}
                | Rego window: {{ (int) ($settings['registration_days'] ?? 30) }} days
                | Service (date) window: {{ (int) ($settings['service_days'] ?? 30) }} days
                | Service (reading) threshold: {{ (int) ($settings['service_reading_threshold'] ?? 500) }}
            </div>

            @if (!$recipient)
                <div class="alert alert-warning mt-3">
                    No email recipient could be found. This uses your organisation billing email (if set), otherwise the first admin user email.
                </div>
            @endif
        </div>
    </div>

    @if (!$regoEnabled && !$serviceEnabled)
        <div class="alert alert-warning">
            Reminders are currently disabled in Settings (rego + servicing tracking are both off).
        </div>
    @endif

    @if ($regoEnabled)
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title" style="font-size:18px; margin-bottom:10px;">Registration (Rego)</h2>

                @php
                    $regoOverdue = $digest['registration']['overdue'] ?? [];
                    $regoDueSoon = $digest['registration']['due_soon'] ?? [];
                @endphp

                @if (empty($regoOverdue) && empty($regoDueSoon))
                    <p class="text-muted fst-italic">No registration reminders.</p>
                @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Vehicle</th>
                                    <th>Rego</th>
                                    <th>Expiry</th>
                                    <th>Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($regoOverdue as $item)
                                    <tr>
                                        <td><span class="badge bg-danger">Overdue</span></td>
                                        <td class="fw-bold">{{ $item['name'] ?? '—' }}</td>
                                        <td>{{ $item['registration_number'] ?? '—' }}</td>
                                        <td>{{ $formatDate($item['date'] ?? null) }}</td>
                                        <td>{{ (int) ($item['days'] ?? 0) }}</td>
                                    </tr>
                                @endforeach
                                @foreach($regoDueSoon as $item)
                                    <tr>
                                        <td><span class="badge bg-warning">Due soon</span></td>
                                        <td class="fw-bold">{{ $item['name'] ?? '—' }}</td>
                                        <td>{{ $item['registration_number'] ?? '—' }}</td>
                                        <td>{{ $formatDate($item['date'] ?? null) }}</td>
                                        <td>{{ (int) ($item['days'] ?? 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if ($serviceEnabled)
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title" style="font-size:18px; margin-bottom:10px;">Servicing (Due by date)</h2>

                @php
                    $serviceDateOverdue = $digest['serviceDate']['overdue'] ?? [];
                    $serviceDateDueSoon = $digest['serviceDate']['due_soon'] ?? [];
                @endphp

                @if (empty($serviceDateOverdue) && empty($serviceDateDueSoon))
                    <p class="text-muted fst-italic">No service-by-date reminders.</p>
                @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Vehicle</th>
                                    <th>Due date</th>
                                    <th>Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($serviceDateOverdue as $item)
                                    <tr>
                                        <td><span class="badge bg-danger">Overdue</span></td>
                                        <td class="fw-bold">{{ $item['name'] ?? '—' }}</td>
                                        <td>{{ $formatDate($item['date'] ?? null) }}</td>
                                        <td>{{ (int) ($item['days'] ?? 0) }}</td>
                                    </tr>
                                @endforeach
                                @foreach($serviceDateDueSoon as $item)
                                    <tr>
                                        <td><span class="badge bg-warning">Due soon</span></td>
                                        <td class="fw-bold">{{ $item['name'] ?? '—' }}</td>
                                        <td>{{ $formatDate($item['date'] ?? null) }}</td>
                                        <td>{{ (int) ($item['days'] ?? 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title" style="font-size:18px; margin-bottom:10px;">Servicing (Due by reading)</h2>

                @php
                    $serviceReadingOverdue = $digest['serviceReading']['overdue'] ?? [];
                    $serviceReadingDueSoon = $digest['serviceReading']['due_soon'] ?? [];
                @endphp

                @if (empty($serviceReadingOverdue) && empty($serviceReadingDueSoon))
                    <p class="text-muted fst-italic">No service-by-reading reminders.</p>
                @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Vehicle</th>
                                    <th>Last</th>
                                    <th>Due</th>
                                    <th>Delta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($serviceReadingOverdue as $item)
                                    <tr>
                                        <td><span class="badge bg-danger">Overdue</span></td>
                                        <td class="fw-bold">{{ $item['name'] ?? '—' }}</td>
                                        <td>{{ $item['last_reading'] ?? '—' }}</td>
                                        <td>{{ $item['due_reading'] ?? '—' }}</td>
                                        <td>{{ $item['delta'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                                @foreach($serviceReadingDueSoon as $item)
                                    <tr>
                                        <td><span class="badge bg-warning">Due soon</span></td>
                                        <td class="fw-bold">{{ $item['name'] ?? '—' }}</td>
                                        <td>{{ $item['last_reading'] ?? '—' }}</td>
                                        <td>{{ $item['due_reading'] ?? '—' }}</td>
                                        <td>{{ $item['delta'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif

</div>

@endsection
