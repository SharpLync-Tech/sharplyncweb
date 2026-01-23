@extends('layouts.sharpfleet')

@section('title', 'Vehicles Out of Service')

@section('sharpfleet-content')
<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Vehicles Out of Service</h1>
                <p class="page-description">Vehicles marked out of service (temporary) and not available for bookings or trips.</p>
            </div>
            <a href="{{ url('/app/sharpfleet/admin/vehicles') }}" class="btn btn-secondary">Back to Vehicles</a>
        </div>
    </div>

    @if(!($hasIsInService ?? false))
        <div class="alert alert-warning">
            Out-of-service tracking isn’t available yet because the database is missing <strong>vehicles.is_in_service</strong>.
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if(($hasIsInService ?? false) && (!isset($vehicles) || $vehicles->count() === 0))
                <p class="text-muted fst-italic">No out-of-service vehicles found.</p>
            @elseif(($hasIsInService ?? false))
                @php
                    $branchMap = collect($branches ?? collect())->keyBy(fn ($b) => (int) ($b->id ?? 0));
                    $showReason = (bool) ($hasOutOfServiceReason ?? false);
                    $showNote = (bool) ($hasOutOfServiceNote ?? false);
                    $showAt = (bool) ($hasOutOfServiceAt ?? false);
                @endphp

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Registration</th>
                                @if(($branchesEnabled ?? false))
                                    <th>Branch</th>
                                @endif
                                @if($showReason)
                                    <th>Reason</th>
                                @endif
                                @if($showNote)
                                    <th>Note</th>
                                @endif
                                @if($showAt)
                                    <th>Marked out</th>
                                @endif
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicles as $v)
                                @php
                                    $branchName = '—';
                                    if (($branchesEnabled ?? false) && isset($v->branch_id)) {
                                        $br = $branchMap->get((int) ($v->branch_id ?? 0));
                                        if ($br) {
                                            $branchName = (string) ($br->name ?? '—');
                                        }
                                    }

                                    $reason = $showReason ? ($v->out_of_service_reason ?? null) : null;
                                    $note = $showNote ? ($v->out_of_service_note ?? null) : null;
                                    $outAt = $showAt ? ($v->out_of_service_at ?? null) : null;
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $v->name }}</td>
                                    <td>{{ $v->registration_number ?: '—' }}</td>
                                    @if(($branchesEnabled ?? false))
                                        <td>{{ $branchName }}</td>
                                    @endif
                                    @if($showReason)
                                        <td>{{ $reason ?: '—' }}</td>
                                    @endif
                                    @if($showNote)
                                        <td>{{ $note ?: '—' }}</td>
                                    @endif
                                    @if($showAt)
                                        <td>{{ $outAt ? (string) $outAt : '—' }}</td>
                                    @endif
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
