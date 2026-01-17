@extends('layouts.sharpfleet')

@section('title', 'User Details')

@section('sharpfleet-content')

@php
    $formatDateTime = function ($value) use ($dateFormat) {
        try {
            if ($value instanceof \Carbon\Carbon) {
                return $value->format($dateFormat . ' H:i');
            }
            return \Carbon\Carbon::parse((string) $value)->format($dateFormat . ' H:i');
        } catch (\Throwable $e) {
            return 'N/A';
        }
    };
    $distanceLabel = $distanceUnit === 'mi' ? 'mi' : 'km';
@endphp

<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">{{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) }}</h1>
                <p class="page-description">{{ $user->email ?? '' }}</p>
            </div>
            <div class="btn-group">
                <a href="{{ url('/app/sharpfleet/admin/users/'.$user->id.'/edit') }}" class="btn btn-primary">Edit User</a>
                <a href="{{ url('/app/sharpfleet/admin/users') }}" class="btn btn-secondary">Back to Users</a>
            </div>
        </div>
    </div>

    <div class="grid grid-3 gap-4 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Total trips</div>
                <div class="stats-number">{{ (int) $totalTrips }}</div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Total hours</div>
                <div class="stats-number">{{ number_format((float) ($totals['hours'] ?? 0), 1) }}</div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Total distance</div>
                <div class="stats-number">{{ number_format((float) ($totals['distance'] ?? 0), 1) }} {{ $distanceLabel }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-2 gap-4 mb-4">
        <div class="card">
            <div class="card-body">
                <h3 class="section-title">Recent activity</h3>
                @if($lastTrip)
                    <div class="mb-1"><strong>Last trip:</strong> {{ $formatDateTime($lastTrip->started_at ?? null) }}</div>
                    <div class="mb-1">
                        <strong>Vehicle:</strong>
                        <a href="{{ url('/app/sharpfleet/admin/vehicles/' . (int) $lastTrip->vehicle_id . '/details') }}">
                            {{ $lastTrip->vehicle_name ?? 'Vehicle' }}
                        </a>
                    </div>
                    <div class="mb-1"><strong>Registration:</strong> {{ $lastTrip->registration_number ?? 'N/A' }}</div>
                    <div class="mb-1">
                        <strong>Reading:</strong>
                        @if($lastTrip->start_km !== null && $lastTrip->end_km !== null)
                            {{ number_format((float) $lastTrip->start_km, 1) }} → {{ number_format((float) $lastTrip->end_km, 1) }}
                        @else
                            N/A
                        @endif
                    </div>
                @else
                    <div class="text-muted">No trips recorded yet.</div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="section-title">Top vehicles used</h3>
                @if($topVehicles->isEmpty())
                    <div class="text-muted">No trip history yet.</div>
                @else
                    <ol class="mb-0">
                        @foreach($topVehicles as $vehicle)
                            <li>
                                <a href="{{ url('/app/sharpfleet/admin/vehicles/' . (int) $vehicle->id . '/details') }}">
                                    {{ $vehicle->name ?? 'Vehicle' }}
                                </a>
                                <span class="text-muted small">({{ (int) ($vehicle->trip_count ?? 0) }} trips)</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="section-title">Totals by period</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Distance ({{ $distanceLabel }})</th>
                            <th>Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>This week</td>
                            <td>{{ number_format((float) ($totals['week_distance'] ?? 0), 1) }}</td>
                            <td>{{ number_format((float) ($totals['week_hours'] ?? 0), 1) }}</td>
                        </tr>
                        <tr>
                            <td>This month</td>
                            <td>{{ number_format((float) ($totals['month_distance'] ?? 0), 1) }}</td>
                            <td>{{ number_format((float) ($totals['month_hours'] ?? 0), 1) }}</td>
                        </tr>
                        <tr>
                            <td>This year</td>
                            <td>{{ number_format((float) ($totals['year_distance'] ?? 0), 1) }}</td>
                            <td>{{ number_format((float) ($totals['year_hours'] ?? 0), 1) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
