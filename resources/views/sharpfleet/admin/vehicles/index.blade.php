@extends('layouts.sharpfleet') 

@section('title', 'Vehicles')

@section('sharpfleet-content')

<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Vehicles</h1>
                <p class="page-description">Manage vehicles for your organisation.</p>
            </div>
            <a href="{{ url('/app/sharpfleet/admin/vehicles/create') }}" class="btn btn-primary">+ Add Vehicle</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if($vehicles->count() === 0)
                <p class="text-muted fst-italic">No vehicles found.</p>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Registration</th>
                                <th>Status</th>
                                <th>Active Trip</th>
                                <th>Type</th>
                                <th>Class</th>
                                <th>Make/Model</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicles as $v)
                                <tr>
                                    <td><a href="{{ url('/app/sharpfleet/admin/vehicles/'.$v->id.'/details') }}">{{ $v->name }}</a></td>
                                    <td>{{ $v->registration_number }}</td>
                                    <td>
                                        @php
                                            $isInService = isset($v->is_in_service) ? (int) $v->is_in_service : 1;
                                            $reason = $v->out_of_service_reason ?? null;
                                            $note = $v->out_of_service_note ?? null;
                                        @endphp

                                        @if($isInService === 0)
                                            <div class="fw-bold text-error">Out of service</div>
                                            <div class="text-muted">
                                                {{ $reason ?: '—' }}
                                            </div>
                                            @if($note)
                                                <div class="text-muted">{{ $note }}</div>
                                            @endif
                                        @else
                                            <span class="text-muted">In service</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($activeTripVehicleIds[$v->id]))
                                            <div class="fw-bold">In trip</div>
                                            <div class="text-muted">
                                                {{ $activeTripsByVehicle[$v->id]['driver_name'] ?? '—' }}
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($v->vehicle_type) }}</td>
                                    <td>{{ $v->vehicle_class ?? '—' }}</td>
                                    <td>{{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) ?: '—' }}</td>
                                    <td>
                                        <div class="btn-group-sm">
                                            <a href="{{ url('/app/sharpfleet/admin/vehicles/'.$v->id.'/edit') }}" class="btn btn-secondary btn-sm">Edit</a>
                                            @if(!empty($isSubscribed))
                                                <a href="{{ url('/app/sharpfleet/admin/vehicles/'.$v->id.'/archive/confirm') }}" class="btn btn-danger btn-sm">Archive</a>
                                            @else
                                                <form method="POST"
                                                    action="{{ url('/app/sharpfleet/admin/vehicles/'.$v->id.'/archive') }}"
                                                    class="d-inline"
                                                    data-sf-confirm
                                                    data-sf-confirm-title="Archive vehicle"
                                                    data-sf-confirm-message="Archive this vehicle? Drivers will no longer be able to select it."
                                                    data-sf-confirm-text="Archive"
                                                    data-sf-confirm-variant="danger">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm">Archive</button>
                                                </form>
                                            @endif
                                        </div>
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
