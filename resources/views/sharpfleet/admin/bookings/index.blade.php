@extends('layouts.sharpfleet')

@section('title', 'Bookings')

@section('sharpfleet-content')

@php($today = \Carbon\Carbon::now()->format('Y-m-d'))

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

                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Start date</label>
                            <input type="date" name="planned_start_date" class="form-control" required min="{{ $today }}" value="{{ old('planned_start_date') }}">
                            @error('planned_start_date')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Start time</label>
                            <div class="grid grid-2">
                                <select name="planned_start_hour" class="form-control" required>
                                    <option value="">HH</option>
                                    @for($h = 0; $h <= 23; $h++)
                                        @php($hh = str_pad((string)$h, 2, '0', STR_PAD_LEFT))
                                        <option value="{{ $hh }}" {{ old('planned_start_hour') === $hh ? 'selected' : '' }}>{{ $hh }}</option>
                                    @endfor
                                </select>
                                <select name="planned_start_minute" class="form-control" required>
                                    <option value="">MM</option>
                                    @for($m = 0; $m <= 59; $m++)
                                        @php($mm = str_pad((string)$m, 2, '0', STR_PAD_LEFT))
                                        <option value="{{ $mm }}" {{ old('planned_start_minute') === $mm ? 'selected' : '' }}>{{ $mm }}</option>
                                    @endfor
                                </select>
                            </div>
                            @error('planned_start_hour')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            @error('planned_start_minute')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">End date</label>
                            <input type="date" name="planned_end_date" class="form-control" required min="{{ $today }}" value="{{ old('planned_end_date') }}">
                            @error('planned_end_date')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">End time</label>
                            <div class="grid grid-2">
                                <select name="planned_end_hour" class="form-control" required>
                                    <option value="">HH</option>
                                    @for($h = 0; $h <= 23; $h++)
                                        @php($hh = str_pad((string)$h, 2, '0', STR_PAD_LEFT))
                                        <option value="{{ $hh }}" {{ old('planned_end_hour') === $hh ? 'selected' : '' }}>{{ $hh }}</option>
                                    @endfor
                                </select>
                                <select name="planned_end_minute" class="form-control" required>
                                    <option value="">MM</option>
                                    @for($m = 0; $m <= 59; $m++)
                                        @php($mm = str_pad((string)$m, 2, '0', STR_PAD_LEFT))
                                        <option value="{{ $mm }}" {{ old('planned_end_minute') === $mm ? 'selected' : '' }}>{{ $mm }}</option>
                                    @endfor
                                </select>
                            </div>
                            @error('planned_end_hour')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            @error('planned_end_minute')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Vehicle (available only)</label>
                        <div id="adminBookingVehicleStatus" class="hint-text">Select start/end date & time to load available vehicles.</div>
                        <select id="adminBookingVehicleSelect" name="vehicle_id" class="form-control" required disabled>
                            <option value="">— Select vehicle —</option>
                        </select>
                        @error('vehicle_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
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

                    <button id="adminCreateBookingBtn" type="submit" class="btn btn-primary" disabled>Create Booking</button>
                </form>

                <script>
                    const adminStartDate = document.querySelector('input[name="planned_start_date"]');
                    const adminStartHour = document.querySelector('select[name="planned_start_hour"]');
                    const adminStartMinute = document.querySelector('select[name="planned_start_minute"]');
                    const adminEndDate = document.querySelector('input[name="planned_end_date"]');
                    const adminEndHour = document.querySelector('select[name="planned_end_hour"]');
                    const adminEndMinute = document.querySelector('select[name="planned_end_minute"]');

                    const adminVehicleSelect = document.getElementById('adminBookingVehicleSelect');
                    const adminVehicleStatus = document.getElementById('adminBookingVehicleStatus');
                    const adminCreateBookingBtn = document.getElementById('adminCreateBookingBtn');

                    function setAdminVehicleOptions(vehicles) {
                        adminVehicleSelect.innerHTML = '';
                        const placeholder = document.createElement('option');
                        placeholder.value = '';
                        placeholder.textContent = vehicles.length ? '— Select vehicle —' : 'No vehicles available for this time window';
                        adminVehicleSelect.appendChild(placeholder);

                        vehicles.forEach(v => {
                            const opt = document.createElement('option');
                            opt.value = v.id;
                            opt.textContent = `${v.name} (${v.registration_number})`;
                            adminVehicleSelect.appendChild(opt);
                        });
                    }

                    function updateAdminCreateButtonState() {
                        const hasVehicle = adminVehicleSelect && adminVehicleSelect.value;
                        if (adminCreateBookingBtn) {
                            adminCreateBookingBtn.disabled = !hasVehicle;
                        }
                    }

                    async function getAdminResponseErrorMessage(res) {
                        try {
                            const data = await res.json();
                            if (data && typeof data.message === 'string' && data.message.trim()) {
                                return data.message;
                            }
                            if (data && data.errors && typeof data.errors === 'object') {
                                const keys = Object.keys(data.errors);
                                for (const k of keys) {
                                    const arr = data.errors[k];
                                    if (Array.isArray(arr) && arr.length && typeof arr[0] === 'string') {
                                        return arr[0];
                                    }
                                }
                            }
                        } catch (e) {
                            // ignore JSON parse failures
                        }
                        return null;
                    }

                    async function loadAdminAvailableVehicles() {
                        if (!adminVehicleSelect || !adminStartDate || !adminStartHour || !adminStartMinute || !adminEndDate || !adminEndHour || !adminEndMinute) return;

                        if (!adminStartDate.value || !adminStartHour.value || !adminStartMinute.value || !adminEndDate.value || !adminEndHour.value || !adminEndMinute.value) {
                            adminVehicleSelect.disabled = true;
                            setAdminVehicleOptions([]);
                            if (adminVehicleStatus) {
                                adminVehicleStatus.textContent = 'Select start/end date & time to load available vehicles.';
                            }
                            updateAdminCreateButtonState();
                            return;
                        }

                        adminVehicleSelect.disabled = true;
                        if (adminVehicleStatus) {
                            adminVehicleStatus.textContent = 'Loading available vehicles...';
                        }

                        const params = new URLSearchParams({
                            planned_start_date: adminStartDate.value,
                            planned_start_hour: adminStartHour.value,
                            planned_start_minute: adminStartMinute.value,
                            planned_end_date: adminEndDate.value,
                            planned_end_hour: adminEndHour.value,
                            planned_end_minute: adminEndMinute.value,
                        });

                        try {
                            const res = await fetch(`/app/sharpfleet/admin/bookings/available-vehicles?${params.toString()}`,
                                {
                                    credentials: 'same-origin',
                                    headers: { 'Accept': 'application/json' },
                                }
                            );
                            if (!res.ok) {
                                const msg = await getAdminResponseErrorMessage(res);
                                adminVehicleSelect.disabled = true;
                                setAdminVehicleOptions([]);
                                if (adminVehicleStatus) {
                                    adminVehicleStatus.textContent = msg || 'Could not load vehicles for that time window.';
                                }
                                updateAdminCreateButtonState();
                                return;
                            }
                            const data = await res.json();
                            const vehicles = Array.isArray(data.vehicles) ? data.vehicles : [];
                            setAdminVehicleOptions(vehicles);
                            adminVehicleSelect.disabled = false;
                            if (adminVehicleStatus) {
                                adminVehicleStatus.textContent = vehicles.length
                                    ? `Available vehicles: ${vehicles.length}`
                                    : 'No vehicles available for this time window.';
                            }
                            updateAdminCreateButtonState();
                        } catch (e) {
                            adminVehicleSelect.disabled = true;
                            setAdminVehicleOptions([]);
                            if (adminVehicleStatus) {
                                adminVehicleStatus.textContent = 'Could not load vehicles (network error).';
                            }
                            updateAdminCreateButtonState();
                        }
                    }

                    [adminStartDate, adminStartHour, adminStartMinute, adminEndDate, adminEndHour, adminEndMinute].forEach(el => {
                        if (el) el.addEventListener('change', loadAdminAvailableVehicles);
                    });

                    if (adminVehicleSelect) {
                        adminVehicleSelect.addEventListener('change', updateAdminCreateButtonState);
                    }

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

                    // Initial state
                    loadAdminAvailableVehicles();
                    updateAdminCreateButtonState();
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
                                        <form method="POST" action="{{ url('/app/sharpfleet/admin/bookings/' . $b->id . '/change-vehicle') }}" class="mb-2">
                                            @csrf
                                            <div class="form-group" style="margin-bottom: 8px;">
                                                <select name="new_vehicle_id" class="form-control" required>
                                                    <option value="">— Change vehicle —</option>
                                                    @foreach($vehicles as $v)
                                                        <option value="{{ $v->id }}" {{ (int)$b->vehicle_id === (int)$v->id ? 'selected' : '' }}>
                                                            {{ $v->name }} ({{ $v->registration_number }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Update</button>
                                        </form>

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
