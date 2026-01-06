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
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $vehicleName ?: '—' }}</td>
                                    <td>{{ $driverName }}</td>
                                    <td>{{ $startedAt ? (string) $startedAt : '—' }}</td>
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
