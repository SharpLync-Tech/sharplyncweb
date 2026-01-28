@extends('layouts.sharpfleet')

@section('title', 'Current Active Trips')

@section('sharpfleet-content')
<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Current Active Trips</h1>
                <p class="page-description">Trips that have started but have not ended yet.</p>
            </div>
            <a href="{{ url('/app/sharpfleet/admin') }}" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    @if(!($tripsTableExists ?? false))
        <div class="alert alert-warning">
            Trips aren’t available yet because the database is missing the <strong>trips</strong> table.
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if(($tripsTableExists ?? false) && (!isset($trips) || $trips->count() === 0))
                <p class="text-muted fst-italic">No active trips found.</p>
            @elseif(($tripsTableExists ?? false))
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Driver</th>
                                <th>Start time</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trips as $t)
                                @php
                                    $vehicleName = (string) ($t->vehicle_name ?? '—');
                                    $rego = (string) ($t->registration_number ?? '');
                                    if ($rego !== '') {
                                        $vehicleName .= ' (' . $rego . ')';
                                    }

                                    $driverName = trim((string)($t->driver_first_name ?? '') . ' ' . (string)($t->driver_last_name ?? ''));
                                    if ($driverName === '') {
                                        $driverName = ($t->driver_id ?? null) ? ('User #' . (int) $t->driver_id) : '—';
                                    }

                                    $startedAt = $t->started_at ?? null;
                                    $tripTz = isset($t->timezone) && trim((string) $t->timezone) !== "" ? (string) $t->timezone : ($companyTimezone ?? "UTC");
                                    $startedAtLabel = "—";
                                    if ($startedAt) {
                                        try {
                                            $startedAtLabel = \Carbon\Carbon::parse($startedAt)->timezone($tripTz)->format("M j, Y g:i A");
                                        } catch (\Throwable $e) {
                                            $startedAtLabel = (string) $startedAt;
                                        }
                                    }

                                    $customerName = trim((string) ($t->customer_name_display ?? $t->customer_name ?? ''));
                                    if ($customerName === '') {
                                        $customerName = '—';
                                    }
                                    $clientPresent = $t->client_present ?? null;
                                    $clientPresentLabel = $clientPresent === null || $clientPresent === '' ? '—' : ((int) $clientPresent === 1 ? 'Yes' : 'No');
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $vehicleName ?: '—' }}</td>
                                    <td>{{ $driverName }}</td>
                                    <td>{{ $startedAtLabel }}</td>
                                    <td>
                                        <details>
                                            <summary>View</summary>
                                            <div class="text-muted" style="margin-top:8px;">
                                                <div><strong>Vehicle:</strong> {{ $vehicleName ?: '—' }}</div>
                                                <div><strong>Driver:</strong> {{ $driverName }}</div>
                                                <div><strong>Customer/Client:</strong> {{ $customerName }}</div>
                                                <div><strong>Started:</strong> {{ $startedAtLabel }}</div>
                                                <div><strong>Client present:</strong> {{ $clientPresentLabel }}</div>
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection





