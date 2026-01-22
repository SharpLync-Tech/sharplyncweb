@extends('layouts.sharpfleet')

@section('title', 'Vehicle Details')

@section('sharpfleet-content')

@php
    $isHours = ($trackingMode ?? 'distance') === 'hours';
    $unitLabel = $isHours ? 'hours' : ($distanceUnit ?? 'km');
    $formatReading = function ($value) use ($isHours) {
        if ($value === null) {
            return 'N/A';
        }
        $decimals = $isHours ? 1 : 0;
        return number_format((float) $value, $decimals);
    };
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
    $insurance = $insurance ?? null;
    $insuranceDocuments = $insuranceDocuments ?? collect();
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
                <div class="mb-2"><strong>Last counter:</strong> {{ $formatReading($lastReading) }} {{ $unitLabel }}</div>
                <div><strong>Total since tracked:</strong> {{ $formatReading($totals['since'] ?? 0) }} {{ $unitLabel }}</div>
                <div><strong>Total this week:</strong> {{ $formatReading($totals['week'] ?? 0) }} {{ $unitLabel }}</div>
                <div><strong>Total this month:</strong> {{ $formatReading($totals['month'] ?? 0) }} {{ $unitLabel }}</div>
                <div><strong>Total this year:</strong> {{ $formatReading($totals['year'] ?? 0) }} {{ $unitLabel }}</div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="section-title">Servicing</h3>
                <div class="mb-2">
                    <strong>Last service:</strong>
                    @if(!empty($lastServiceReading))
                        {{ $formatReading((float) $lastServiceReading) }} {{ $unitLabel }}
                    @else
                        N/A
                    @endif
                </div>
                <div class="mb-2">
                    <strong>Last service date:</strong>
                    {{ $lastServiceDate ? $formatDate($lastServiceDate) : 'N/A' }}
                </div>
                <div class="mb-2">
                    <strong>Next service due:</strong>
                    @if(!empty($serviceDueReading))
                        {{ $formatReading((float) $serviceDueReading) }} {{ $unitLabel }}
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

    <div class="card mb-3">
        <div class="card-body">
            <h3 class="section-title">Insurance</h3>
            @if(!$insurance)
                <div class="text-muted">No insurance details added.</div>
            @else
                <div class="mb-2"><strong>Company:</strong> {{ $insurance->insurance_company ?: 'N/A' }}</div>
                <div class="mb-2"><strong>Policy number:</strong> {{ $insurance->policy_number ?: 'N/A' }}</div>
                <div class="mb-2">
                    <strong>Cover type:</strong>
                    @php
                        $policyTypeMap = [
                            'comprehensive' => 'Comprehensive',
                            'third_party' => 'Third party',
                            'third_party_fire_theft' => 'Third party fire & theft',
                            'uninsured' => 'Uninsured',
                        ];
                        $policyType = $insurance->policy_type ?? '';
                    @endphp
                    {{ $policyTypeMap[$policyType] ?? 'N/A' }}
                </div>
                <div class="mb-2"><strong>Expiry date:</strong> {{ $insurance->expiry_date ? $formatDate($insurance->expiry_date) : 'N/A' }}</div>
                <div class="mb-2"><strong>Notify email:</strong> {{ $insurance->notify_email ?: 'N/A' }}</div>
                <div class="mb-2"><strong>Notify window:</strong> {{ $insurance->notify_window_days !== null ? ((int) $insurance->notify_window_days . ' days') : 'N/A' }}</div>
                <div>
                    <strong>Documents:</strong>
                    @if($insuranceDocuments->isNotEmpty())
                        <div class="mt-1">
                            @foreach($insuranceDocuments as $doc)
                                <div>
                                    <a href="{{ url('/app/sharpfleet/admin/vehicles/'.$vehicle->id.'/insurance-document/'.(int) $doc->id) }}">
                                        {{ $doc->document_original_name ?: 'Insurance document' }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @elseif(!empty($insurance->policy_document_original_name))
                        <a href="{{ url('/app/sharpfleet/admin/vehicles/'.$vehicle->id.'/insurance-document') }}">{{ $insurance->policy_document_original_name }}</a>
                    @else
                        N/A
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h3 class="section-title">Fuel receipts (latest 5)</h3>
            @if(($fuelReceipts ?? collect())->isEmpty())
                <div class="text-muted">No fuel receipts uploaded.</div>
            @else
                <div class="d-flex flex-column gap-2">
                    @foreach($fuelReceipts as $receipt)
                        <div class="d-flex align-items-center gap-2" style="flex-wrap: wrap;">
                            <form method="POST" action="{{ url('/app/sharpfleet/admin/vehicles/' . (int) $vehicle->id . '/fuel-receipts/' . (int) $receipt->id . '/delete') }}" data-sf-confirm data-sf-confirm-title="Delete receipt?" data-sf-confirm-message="This will delete the receipt image. Continue?" style="margin:0;">
                                @csrf
                                <button type="submit" class="btn btn-link" style="padding: 4px; color: #d84b4b; text-decoration: none;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                        <path fill="currentColor" d="M9 3h6l1 2h4v2H4V5h4l1-2zm1 6h2v9h-2V9zm4 0h2v9h-2V9zM7 9h2v9H7V9z"/>
                                    </svg>
                                </button>
                            </form>
                            <a href="{{ url('/app/sharpfleet/admin/vehicles/' . (int) $vehicle->id . '/fuel-receipts/' . (int) $receipt->id . '/image') }}" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; gap: 10px; text-decoration: none;">
                                <img
                                    src="{{ url('/app/sharpfleet/admin/vehicles/' . (int) $vehicle->id . '/fuel-receipts/' . (int) $receipt->id . '/image') }}"
                                    alt="Fuel receipt"
                                    style="width: 120px; height: 90px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(10, 42, 77, 0.12);"
                                >
                                <div class="text-muted small">
                                    {{ $formatDate($receipt->created_at ?? null) }}
                                    @if(!empty($receipt->odometer_reading))
                                        <div>Odometer: {{ number_format((int) $receipt->odometer_reading) }}</div>
                                    @endif
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
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
