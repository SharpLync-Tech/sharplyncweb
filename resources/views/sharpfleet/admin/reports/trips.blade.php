@extends('layouts.sharpfleet')

@section('title', 'Trip Reports')

@section('sharpfleet-content')

@php
    $companyTimezone = $companyTimezone ?? config('app.timezone');
    $uiVehicleId = $ui['vehicle_id'] ?? request('vehicle_id');
    $uiStartDate = $ui['start_date'] ?? request('start_date');
    $uiEndDate = $ui['end_date'] ?? request('end_date');
    $uiCustomerId = $ui['customer_id'] ?? request('customer_id');
    $uiBranchIds = $ui['branch_ids'] ?? request('branch_ids', []);
    $uiBranchIds = is_array($uiBranchIds) ? $uiBranchIds : [$uiBranchIds];
    $showBranchFilter = (bool) ($ui['show_branch_filter'] ?? false);
    $filtersGridClass = $showBranchFilter ? 'grid grid-4' : 'grid grid-3';
@endphp

<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Trip Reports</h1>
                <p class="page-description">View and export trip data for reporting.</p>
            </div>
            <div class="btn-group">
                <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}" class="d-inline">
                    <input type="hidden" name="export" value="csv">
                    <input type="hidden" name="vehicle_id" value="{{ $uiVehicleId }}">
                    <input type="hidden" name="start_date" value="{{ $uiStartDate }}">
                    <input type="hidden" name="end_date" value="{{ $uiEndDate }}">
                    <input type="hidden" name="customer_id" value="{{ $uiCustomerId }}">
                    @foreach($uiBranchIds as $bid)
                        @if(is_numeric($bid) && (int)$bid > 0)
                            <input type="hidden" name="branch_ids[]" value="{{ (int)$bid }}">
                        @endif
                    @endforeach
                    <button type="submit" class="btn btn-primary">Export CSV</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ url('/app/sharpfleet/admin/reports/trips') }}">
                <div class="{{ $filtersGridClass }}">
                    <div class="form-group">
                        <label class="form-label">Vehicle</label>
                        <select name="vehicle_id" class="form-control">
                            <option value="">All Vehicles</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ (string)$uiVehicleId === (string)$vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->name }} ({{ $vehicle->registration_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" value="{{ $uiStartDate }}" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" value="{{ $uiEndDate }}" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-secondary mt-3">Filter</button>

                <div class="mt-2 text-muted">
                    Times shown in {{ $companyTimezone }}
                </div>
            </form>
        </div>
    </div>

    {{-- Results --}}
    <div class="card">
        <div class="card-body">
            @if($trips->count() === 0)
                <p class="text-muted fst-italic">No trips found matching the filters.</p>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Driver</th>
                                <th>Trip Mode</th>
                                <th>Customer</th>
                                <th>Started At</th>
                                <th>Ended At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trips as $t)
                                <tr>
                                    <td class="fw-bold">
                                        {{ $t->vehicle_name }}<br>
                                        <small class="text-muted">{{ $t->registration_number }}</small>
                                    </td>
                                    <td>{{ $t->driver_name }}</td>
                                    <td>{{ strtolower($t->trip_mode) === 'private' ? 'Private' : 'Business' }}</td>

                                    {{-- CUSTOMER --}}
                                    <td>
                                        {{ $t->customer_name_display ?: '—' }}

                                        @if(!$t->customer_id && $t->customer_name_display)
                                            <div class="mt-1">
                                                <a
                                                    href="#"
                                                    class="text-primary small"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#convertCustomerModal"
                                                    data-trip-id="{{ $t->id }}"
                                                    data-customer-name="{{ $t->customer_name_display }}"
                                                >
                                                    Convert to customer
                                                </a>
                                            </div>
                                        @endif
                                    </td>

                                    <td>{{ \Carbon\Carbon::parse($t->started_at)->timezone($companyTimezone)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        {{ $t->end_time
                                            ? \Carbon\Carbon::parse($t->end_time)->timezone($companyTimezone)->format('d/m/Y H:i')
                                            : '—'
                                        }}
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

{{-- Convert to Customer Modal --}}
<div class="modal fade" id="convertCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.trips.convertCustomer') }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Convert to Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="trip_id" id="convert_trip_id">

                    <div class="mb-3">
                        <label class="form-label">Customer name</label>
                        <input
                            type="text"
                            class="form-control"
                            name="name"
                            id="convert_customer_name"
                            required
                        >
                        <div class="form-text">
                            You can edit this before creating the customer.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes (optional)</label>
                        <textarea
                            class="form-control"
                            name="notes"
                            rows="2"
                        ></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Create customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const convertModal = document.getElementById('convertCustomerModal');

convertModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const tripId = button.getAttribute('data-trip-id');
    const name = button.getAttribute('data-customer-name');

    convertModal.querySelector('#convert_trip_id').value = tripId;
    convertModal.querySelector('#convert_customer_name').value = name;
});
</script>

@endsection
