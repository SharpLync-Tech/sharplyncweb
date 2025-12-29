@extends('admin.layouts.admin-layout')

@section('title', 'SharpFleet Vehicle')

@section('content')
<div class="container-fluid">
    <div class="sl-page-header d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h2 class="fw-semibold">{{ $vehicle->name ?? 'Vehicle' }}</h2>
            <div class="sl-subtitle small">
                @if(!empty($organisation))
                    {{ $organisation->name ?? 'Organisation' }} (ID: {{ $organisation->id ?? '' }})
                @else
                    SharpFleet vehicle details
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            @if(!empty($vehicle->organisation_id))
                <a class="btn btn-outline-secondary" href="{{ route('admin.sharpfleet.organisations.vehicles', (int)$vehicle->organisation_id) }}">Back to vehicles</a>
            @else
                <a class="btn btn-outline-secondary" href="{{ route('admin.sharpfleet.platform') }}">Back</a>
            @endif
        </div>
    </div>

    <div class="card sl-card">
        <div class="card-header py-3">
            <div class="fw-semibold">Vehicle record</div>
            <div class="text-muted small">All times shown in AEST (Brisbane time).</div>
        </div>
        <div class="card-body">
            @if(empty($columns))
                <div class="text-muted">Could not read vehicle columns from schema; showing common fields only.</div>
                <div class="mt-3">
                    <div><span class="text-muted">ID:</span> {{ $vehicle->id ?? '—' }}</div>
                    <div><span class="text-muted">Organisation ID:</span> {{ $vehicle->organisation_id ?? '—' }}</div>
                    <div><span class="text-muted">Name:</span> {{ $vehicle->name ?? '—' }}</div>
                    <div><span class="text-muted">Registration:</span> {{ $vehicle->registration_number ?? '—' }}</div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <tbody>
                            @foreach($columns as $col)
                                <tr>
                                    @php
                                        $label = match((string) $col) {
                                            'id' => 'ID',
                                            'organisation_id' => 'Organisation ID',
                                            'registration_number' => 'Registration',
                                            'is_active' => 'Active',
                                            'created_at' => 'Created',
                                            'updated_at' => 'Updated',
                                            default => ucwords(str_replace('_', ' ', (string) $col)),
                                        };
                                    @endphp
                                    <td class="text-muted" style="width: 220px;">{{ $label }}</td>
                                    <td>
                                        @php $val = $vehicle->{$col} ?? null; @endphp
                                        @if(is_null($val) || $val === '')
                                            —
                                        @else
                                            @php
                                                $stringVal = is_scalar($val) ? (string) $val : null;
                                                $isDateLike = is_string($stringVal) && (str_ends_with((string) $col, '_at') || str_contains((string) $col, 'date'));
                                            @endphp
                                            @if($isDateLike)
                                                @php
                                                    $formatted = null;
                                                    try {
                                                        $formatted = \Carbon\Carbon::parse($stringVal, 'UTC')->timezone('Australia/Brisbane')->format('d M Y, H:i');
                                                    } catch (\Throwable $e) {
                                                        $formatted = null;
                                                    }
                                                @endphp
                                                {{ $formatted ?? $stringVal }}
                                            @else
                                                {{ is_scalar($val) ? $val : json_encode($val) }}
                                            @endif
                                        @endif
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
