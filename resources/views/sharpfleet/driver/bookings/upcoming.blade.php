@extends('layouts.sharpfleet')

@section('title', 'Bookings')

@section('sharpfleet-content')

@php
    $user = session('sharpfleet.user');
@endphp

<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Bookings</h1>
                <p class="page-description">Book a vehicle for a specific time window. Only the driver who booked it can start a trip during that window.</p>
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
            <p class="card-subtitle">Date and time are required. Customer/client is optional.</p>
        </div>
        <div class="card-body">
            @if(!$bookingsTableExists)
                <p class="text-muted fst-italic">Bookings are unavailable until the database table is created.</p>
            @else
                <form method="POST" action="{{ url('/app/sharpfleet/bookings') }}">
                    @csrf

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
                        <label class="form-label">Vehicle (available only)</label>
                        <div id="bookingVehicleStatus" class="hint-text">Select start/end date & time to load available vehicles.</div>
                        <select id="bookingVehicleSelect" name="vehicle_id" class="form-control" required disabled>
                            <option value="">— Select vehicle —</option>
                        </select>
                        @error('vehicle_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Customer / Client (optional)</label>
                        @if($customersTableExists && $customers->count() > 0)
                            <select id="bookingCustomerSelect" name="customer_id" class="form-control">
                                <option value="">— Select from list —</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="hint-text">If the customer isn’t in the list, type a name below.</div>
                        @endif

                        <input id="bookingCustomerNameInput" type="text" name="customer_name" class="form-control mt-2" maxlength="150" placeholder="Or enter customer name" value="{{ old('customer_name') }}">
                        @error('customer_name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes for admin/driver">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <button id="createBookingBtn" type="submit" class="btn btn-primary btn-full" disabled>Create Booking</button>
                </form>

                <script>
                    const startDate = document.querySelector('input[name="planned_start_date"]');
                    const startTime = document.querySelector('input[name="planned_start_time"]');
                    const endDate = document.querySelector('input[name="planned_end_date"]');
                    const endTime = document.querySelector('input[name="planned_end_time"]');

                    const bookingVehicleSelect = document.getElementById('bookingVehicleSelect');
                    const bookingVehicleStatus = document.getElementById('bookingVehicleStatus');
                    const createBookingBtn = document.getElementById('createBookingBtn');

                    function setVehicleSelectOptions(vehicles) {
                        bookingVehicleSelect.innerHTML = '';
                        const placeholder = document.createElement('option');
                        placeholder.value = '';
                        placeholder.textContent = vehicles.length ? '— Select vehicle —' : 'No vehicles available for this time window';
                        bookingVehicleSelect.appendChild(placeholder);

                        vehicles.forEach(v => {
                            const opt = document.createElement('option');
                            opt.value = v.id;
                            opt.textContent = `${v.name} (${v.registration_number})`;
                            bookingVehicleSelect.appendChild(opt);
                        });
                    }

                    function updateCreateButtonState() {
                        const hasVehicle = bookingVehicleSelect && bookingVehicleSelect.value;
                        if (createBookingBtn) {
                            createBookingBtn.disabled = !hasVehicle;
                        }
                    }

                    async function loadAvailableVehicles() {
                        if (!bookingVehicleSelect || !startDate || !startTime || !endDate || !endTime) return;

                        if (!startDate.value || !startTime.value || !endDate.value || !endTime.value) {
                            bookingVehicleSelect.disabled = true;
                            setVehicleSelectOptions([]);
                            if (bookingVehicleStatus) {
                                bookingVehicleStatus.textContent = 'Select start/end date & time to load available vehicles.';
                            }
                            updateCreateButtonState();
                            return;
                        }

                        bookingVehicleSelect.disabled = true;
                        if (bookingVehicleStatus) {
                            bookingVehicleStatus.textContent = 'Loading available vehicles...';
                        }

                        const params = new URLSearchParams({
                            planned_start_date: startDate.value,
                            planned_start_time: startTime.value,
                            planned_end_date: endDate.value,
                            planned_end_time: endTime.value,
                        });

                        try {
                            const res = await fetch(`{{ url('/app/sharpfleet/bookings/available-vehicles') }}?${params.toString()}`);
                            if (!res.ok) {
                                bookingVehicleSelect.disabled = true;
                                setVehicleSelectOptions([]);
                                if (bookingVehicleStatus) {
                                    bookingVehicleStatus.textContent = 'Could not load vehicles for that time window.';
                                }
                                updateCreateButtonState();
                                return;
                            }
                            const data = await res.json();
                            const vehicles = Array.isArray(data.vehicles) ? data.vehicles : [];
                            setVehicleSelectOptions(vehicles);
                            bookingVehicleSelect.disabled = false;
                            if (bookingVehicleStatus) {
                                bookingVehicleStatus.textContent = vehicles.length
                                    ? `Available vehicles: ${vehicles.length}`
                                    : 'No vehicles available for this time window.';
                            }
                            updateCreateButtonState();
                        } catch (e) {
                            bookingVehicleSelect.disabled = true;
                            setVehicleSelectOptions([]);
                            if (bookingVehicleStatus) {
                                bookingVehicleStatus.textContent = 'Could not load vehicles (network error).';
                            }
                            updateCreateButtonState();
                        }
                    }

                    [startDate, startTime, endDate, endTime].forEach(el => {
                        if (el) el.addEventListener('change', loadAvailableVehicles);
                    });

                    if (bookingVehicleSelect) {
                        bookingVehicleSelect.addEventListener('change', updateCreateButtonState);
                    }

                    const bookingCustomerSelect = document.getElementById('bookingCustomerSelect');
                    const bookingCustomerNameInput = document.getElementById('bookingCustomerNameInput');

                    if (bookingCustomerSelect && bookingCustomerNameInput) {
                        bookingCustomerSelect.addEventListener('change', () => {
                            if (bookingCustomerSelect.value) {
                                bookingCustomerNameInput.value = '';
                            }
                        });

                        bookingCustomerNameInput.addEventListener('input', () => {
                            if (bookingCustomerNameInput.value.trim()) {
                                bookingCustomerSelect.value = '';
                            }
                        });
                    }

                    // Initial state
                    loadAvailableVehicles();
                    updateCreateButtonState();
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
                                @php($isMine = $user && (int)$user['id'] === (int)$b->user_id)
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
                                        @if($isMine)
                                            <form method="POST" action="{{ url('/app/sharpfleet/bookings/' . $b->id . '/cancel') }}">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary">Cancel</button>
                                            </form>
                                        @else
                                            <span class="text-muted">—</span>
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
