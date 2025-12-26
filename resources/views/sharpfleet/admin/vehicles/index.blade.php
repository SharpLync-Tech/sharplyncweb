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
                                <th>Rego</th>
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
                                    <td class="fw-bold">{{ $v->name }}</td>
                                    <td>{{ $v->registration_number }}</td>
                                    <td>
                                        @if(!empty($activeTripVehicleIds[$v->id]))
                                            <span class="fw-bold">In trip</span>
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
                                            <form method="POST" action="{{ url('/app/sharpfleet/admin/vehicles/'.$v->id.'/archive') }}"
                                                  onsubmit="return confirm('Archive this vehicle? Drivers will no longer be able to select it.');" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm">Archive</button>
                                            </form>
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
