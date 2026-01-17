@extends('layouts.sharpfleet')

@section('title', 'Vehicle Details')

@section('sharpfleet-content')

@php
    $unitLabel = ($trackingMode ?? 'distance') === 'hours' ? 'hours' : ($distanceUnit ?? 'km');
    $formatDate = function ($value) use ($dateFormat) {
        try {
            if ($value instanceof \Carbon\Carbon) {
                return $value->format($dateFormat);
            }
            return \Carbon\Carbon::parse((string) $value)->format($dateFormat);
        } catch (\Throwable $e) {
            return 'N/A';
        }
    };
@endphp

<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">{{ $vehicle->name ?? 'Vehicle' }}</h1>
                <p class="page-description">Registration: {{ $vehicle->registration_number ?: 'Not set' }}</p>
                @if(!empty($vehicle->variant))
                    <div class="text-muted">Variant: {{ $vehicle->variant }}</div>
                @endif
            </div>
            <div class="btn-group">
                <a href="{{ url('/app/sharpfleet/admin/vehicles/' . (int) $vehicle->id . '/edit') }}" class="btn btn-primary">Edit Vehicle</a>
                <a href="{{ url('/app/sharpfleet/admin/vehicles') }}" class="btn btn-secondary">Back to Vehicles</a>
            </div>
        </div>
    </div>

    <div class="grid grid-2 gap-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h3 class="section-title">Usage summary</h3>
                <div class="mb-2"><strong>Last counter:</strong> {{ $lastReading !== null ? number_format($lastReading, 1) : 'N/A' }} {{ $unitLabel }}</div>
                <div><strong>Total since tracked:</strong> {{ number_format($totals['since'] ?? 0, 1) }} {{ $unitLabel }}</div>
                <div><strong>Total this week:</strong> {{ number_format($totals['week'] ?? 0, 1) }} {{ $unitLabel }}</div>
                <div><strong>Total this month:</strong> {{ number_format($totals['month'] ?? 0, 1) }} {{ $unitLabel }}</div>
                <div><strong>Total this year:</strong> {{ number_format($totals['year'] ?? 0, 1) }} {{ $unitLabel }}</div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="section-title">Servicing</h3>
                <div class="mb-2"><strong>Last service reading:</strong> {{ $lastReading !== null ? number_format($lastReading, 1) : 'N/A' }} {{ $unitLabel }}</div>
                <div class="mb-2">
                    <strong>Next service due:</strong>
                    @if(!empty($serviceDueReading))
                        {{ number_format((float) $serviceDueReading, 1) }} {{ $unitLabel }}
                    @else
                        N/A
                    @endif
                </div>
                <div>
                    <strong>Next service due date:</strong>
                    {{ $serviceDueDate ? $formatDate($serviceDueDate) : 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-2 gap-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h3 class="section-title">Top drivers</h3>
                @if($drivers->isEmpty())
                    <div class="text-muted">No trips recorded yet.</div>
                @else
                    <ol class="mb-0">
                        @foreach($drivers as $driver)
                            <li>{{ $driver->driver_name ?: 'Unknown' }} ({{ (int) ($driver->trip_count ?? 0) }} trips)</li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="section-title">Top customers / jobs</h3>
                @if($customers->isEmpty())
                    <div class="text-muted">No customer data recorded yet.</div>
                @else
                    <ol class="mb-0">
                        @foreach($customers as $customer)
                            <li>{{ $customer->customer_name_display ?: 'Unknown' }} ({{ (int) ($customer->trip_count ?? 0) }} trips)</li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-2 gap-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h3 class="section-title">Vehicle age</h3>
                @if($age)
                    <div>{{ $age['years'] }} years, {{ $age['months'] }} months</div>
                @else
                    <div class="text-muted">First registration year not set.</div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="section-title">Assignment</h3>
                <div>{{ $assignment ?: 'Not available' }}</div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h3 class="section-title">Last 5 faults</h3>
            @if($faults->isEmpty())
                <div class="text-muted">No faults reported.</div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Severity</th>
                                <th>Status</th>
                                <th>Reporter</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($faults as $fault)
                                <tr>
                                    <td>{{ $formatDate($fault->created_at ?? null) }}</td>
                                    <td>{{ ucfirst((string) ($fault->severity ?? '')) }}</td>
                                    <td>{{ ucfirst((string) ($fault->status ?? '')) }}</td>
                                    <td>{{ $fault->reporter_name ?: 'Unknown' }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $fault->title ?: 'Issue' }}</div>
                                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit((string) ($fault->description ?? ''), 120) }}</div>
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
