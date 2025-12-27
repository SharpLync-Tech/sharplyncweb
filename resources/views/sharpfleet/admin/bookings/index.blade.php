@extends('layouts.sharpfleet')

@section('title', 'Bookings')

@section('sharpfleet-content')

<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Bookings</h1>
                <p class="page-description">Manage upcoming bookings. A vehicle cannot be used during a booked window except by the booking owner.</p>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            Please fix the highlighted fields and try again.
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Create Booking</h2>
            <p class="card-subtitle">Driver, vehicle, date and time are required. Customer/client is optional.</p>
        </div>
        <div class="card-body">
            @if(!$bookingsTableExists)
                <p class="text-muted fst-italic">Bookings are unavailable until the database table is created.</p>
            @else
                <form method="POST" action="{{ url('/app/sharpfleet/admin/bookings') }}">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Driver</label>
                        <select name="user_id" class="form-control" required>
                            <option value="">— Select driver —</option>
                            @foreach($drivers as $d)
                                <option value="{{ $d->id }}" {{ old('user_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->first_name }} {{ $d->last_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Vehicle</label>
                        <select name="vehicle_id" class="form-control" required>
                            <option value="">— Select vehicle —</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}" {{ old('vehicle_id') == $v->id ? 'selected' : '' }}>
                                    {{ $v->name }} ({{ $v->registration_number }})
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Start date</label>
                            <input type="date" name="planned_start_date" class="form-control" required value="{{ old('planned_start_date') }}">
                            @error('planned_start_date')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Start time</label>
                            <input type="time" name="planned_start_time" class="form-control" required value="{{ old('planned_start_time') }}">
                            @error('planned_start_time')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">End date</label>
                            <input type="date" name="planned_end_date" class="form-control" required value="{{ old('planned_end_date') }}">
                            @error('planned_end_date')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">End time</label>
                            <input type="time" name="planned_end_time" class="form-control" required value="{{ old('planned_end_time') }}">
                            @error('planned_end_time')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Customer / Client (optional)</label>
                        @if($customersTableExists && $customers->count() > 0)
                            <select id="adminBookingCustomerSelect" name="customer_id" class="form-control">
                                <option value="">— Select from list —</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="hint-text">If the customer isn’t in the list, type a name below.</div>
                        @endif

                        <input id="adminBookingCustomerNameInput" type="text" name="customer_name" class="form-control mt-2" maxlength="150" placeholder="Or enter customer name" value="{{ old('customer_name') }}">
                        @error('customer_name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Create Booking</button>
                </form>

                <script>
                    const adminBookingCustomerSelect = document.getElementById('adminBookingCustomerSelect');
                    const adminBookingCustomerNameInput = document.getElementById('adminBookingCustomerNameInput');

                    if (adminBookingCustomerSelect && adminBookingCustomerNameInput) {
                        adminBookingCustomerSelect.addEventListener('change', () => {
                            if (adminBookingCustomerSelect.value) {
                                adminBookingCustomerNameInput.value = '';
                            }
                        });

                        adminBookingCustomerNameInput.addEventListener('input', () => {
                            if (adminBookingCustomerNameInput.value.trim()) {
                                adminBookingCustomerSelect.value = '';
                            }
                        });
                    }
                </script>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Upcoming Bookings</h2>
        </div>
        <div class="card-body">
            @if(!$bookingsTableExists)
                <p class="text-muted fst-italic">Bookings are unavailable until the database table is created.</p>
            @elseif($bookings->count() === 0)
                <p class="text-muted fst-italic">No upcoming bookings.</p>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Driver</th>
                                <th>Customer</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $b)
                                <tr>
                                    <td class="fw-bold">
                                        {{ $b->vehicle_name ?: '—' }}
                                        @if($b->registration_number)
                                            <br><small class="text-muted">{{ $b->registration_number }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $b->driver_name }}</td>
                                    <td>{{ $b->customer_name_display ?: '—' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($b->planned_start)->format('d/m/Y H:i') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($b->planned_end)->format('d/m/Y H:i') }}</td>
                                    <td>{{ ucfirst($b->status) }}</td>
                                    <td>
                                        <form method="POST" action="{{ url('/app/sharpfleet/admin/bookings/' . $b->id . '/cancel') }}">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary">Cancel</button>
                                        </form>
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
