@extends('layouts.sharpfleet')

@section('title', 'Permanently Assigned Vehicles')

@section('sharpfleet-content')
<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Permanently Assigned Vehicles</h1>
                <p class="page-description">Vehicles with permanent allocation enabled and the driver assigned.</p>
            </div>
            <a href="{{ url('/app/sharpfleet/admin/vehicles') }}" class="btn btn-secondary">Back to Vehicles</a>
        </div>
    </div>

    @if(!$vehiclesHaveAssignment)
        <div class="alert alert-warning">
            Permanent allocation isn’t available yet because the database is missing <strong>vehicles.assignment_type</strong> and/or <strong>vehicles.assigned_driver_id</strong>.
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if($vehiclesHaveAssignment && (!isset($vehicles) || $vehicles->count() === 0))
                <p class="text-muted fst-italic">No permanently assigned vehicles found.</p>
            @elseif($vehiclesHaveAssignment)
                @php
                    $branchMap = collect($branches ?? collect())->keyBy(fn ($b) => (int) ($b->id ?? 0));
                    $hasOutOfServiceSupport = isset($vehicles) && $vehicles->count() > 0 && property_exists($vehicles->first(), 'is_in_service');
                @endphp

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Rego</th>
                                @if(($branchesEnabled ?? false))
                                    <th>Branch</th>
                                @endif
                                @if($hasOutOfServiceSupport)
                                    <th>Status</th>
                                @endif
                                <th>Assigned driver</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicles as $v)
                                @php
                                    $driverName = trim((string)($v->driver_first_name ?? '') . ' ' . (string)($v->driver_last_name ?? ''));
                                    if ($driverName === '') {
                                        $driverName = 'User #' . (int) ($v->assigned_driver_id ?? 0);
                                    }

                                    $branchName = '—';
                                    if (($branchesEnabled ?? false) && isset($v->branch_id)) {
                                        $br = $branchMap->get((int) ($v->branch_id ?? 0));
                                        if ($br) {
                                            $branchName = (string) ($br->name ?? '—');
                                        }
                                    }

                                    $isInService = isset($v->is_in_service) ? (int) ($v->is_in_service ?? 1) : 1;
                                    $reason = $v->out_of_service_reason ?? null;
                                    $note = $v->out_of_service_note ?? null;
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $v->name }}</td>
                                    <td>{{ $v->registration_number ?: '—' }}</td>
                                    @if(($branchesEnabled ?? false))
                                        <td>{{ $branchName }}</td>
                                    @endif
                                    @if($hasOutOfServiceSupport)
                                        <td>
                                            @if($isInService === 0)
                                                <div class="fw-bold text-error">Out of service</div>
                                                <div class="text-muted">{{ $reason ?: '—' }}</div>
                                                @if($note)
                                                    <div class="text-muted">{{ $note }}</div>
                                                @endif
                                            @else
                                                <span class="text-muted">In service</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td>{{ $driverName }}</td>
                                    <td class="text-right">
                                        <a class="btn btn-secondary btn-sm" href="{{ url('/app/sharpfleet/admin/vehicles/' . (int) $v->id . '/edit') }}">Edit</a>
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
