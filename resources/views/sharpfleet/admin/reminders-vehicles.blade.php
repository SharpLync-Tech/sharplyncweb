@extends('layouts.sharpfleet')

@section('title', 'Vehicle Reminders')

@section('sharpfleet-content')

<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Vehicle Reminders</h1>
                <p class="page-description">Vehicles with upcoming registration or servicing reminders.</p>
            </div>
            <a href="/app/sharpfleet/admin/vehicles" class="btn btn-secondary">View All Vehicles</a>
        </div>
    </div>

    @if (!$regoEnabled && !$serviceEnabled)
        <div class="alert alert-warning">
            Reminders are currently disabled in Settings (registration + servicing tracking are both off).
        </div>
    @endif

    @if (empty($items))
        <div class="alert alert-info">
            No vehicles have reminders due soon.
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Reminder</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td class="fw-bold">
                                        <a href="{{ url('/app/sharpfleet/admin/vehicles/' . (int) $item['vehicle_id'] . '/details') }}">
                                            {{ $item['vehicle_name'] ?: 'Unnamed vehicle' }}
                                        </a>
                                        @if(!empty($item['registration_number']))
                                            <div class="text-muted small">{{ $item['registration_number'] }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ ($item['status'] ?? '') === 'overdue' ? 'bg-danger' : 'bg-warning' }}">
                                            {{ ($item['status'] ?? '') === 'overdue' ? 'Overdue' : 'Due soon' }}
                                        </span>
                                        <span class="ms-2">{{ $item['label'] ?? '' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

@endsection
