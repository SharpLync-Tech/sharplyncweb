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
                                    <td class="text-muted" style="width: 220px;">{{ $col }}</td>
                                    <td>
                                        @php $val = $vehicle->{$col} ?? null; @endphp
                                        @if(is_null($val) || $val === '')
                                            —
                                        @else
                                            {{ is_scalar($val) ? $val : json_encode($val) }}
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
