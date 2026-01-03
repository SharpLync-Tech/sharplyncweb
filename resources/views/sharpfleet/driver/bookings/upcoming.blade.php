@extends('layouts.sharpfleet')

@section('title', 'Bookings')

@section('sharpfleet-content')

@php
    use App\Services\SharpFleet\CompanySettingsService;

    $user = session('sharpfleet.user');
    $settingsService = new CompanySettingsService((int) $user['organisation_id']);
    $companyTimezone = $defaultTimezone ?? $settingsService->timezone();
    $today = \Carbon\Carbon::now($companyTimezone)->format('Y-m-d');

    $branchesEnabled = (bool) ($branchesEnabled ?? false);
    $branches = $branches ?? collect();
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

    <div class="alert alert-info">
        Bookings require an internet connection to check availability and prevent double-booking.
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

                    @if($branchesEnabled && $branches->count() > 1)
                        <div class="form-group">
                            <label class="form-label">Branch</label>
                            <select id="bookingBranchSelect" name="branch_id" class="form-control" required>
                                <option value="">— Select branch —</option>
                                @foreach($branches as $br)
                                    <option value="{{ $br->id }}" {{ (string)old('branch_id') === (string)$br->id ? 'selected' : '' }}>
                                        {{ $br->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    @elseif($branchesEnabled && $branches->count() === 1)
                        <input type="hidden" id="bookingBranchSelect" name="branch_id" value="{{ (int) ($branches->first()->id ?? 0) }}">
                    @endif

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
                    const startHour = document.querySelector('select[name="planned_start_hour"]');
                    const startMinute = document.querySelector('select[name="planned_start_minute"]');
                    const endDate = document.querySelector('input[name="planned_end_date"]');
                    const endHour = document.querySelector('select[name="planned_end_hour"]');
                    const endMinute = document.querySelector('select[name="planned_end_minute"]');

                    const branchSelect = document.getElementById('bookingBranchSelect');

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

                    async function getResponseErrorMessage(res) {
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

                    async function loadAvailableVehicles() {
                        if (!bookingVehicleSelect || !startDate || !startHour || !startMinute || !endDate || !endHour || !endMinute) return;

                        const branchId = branchSelect ? branchSelect.value : '';
                        if (branchSelect && !branchId) {
                            bookingVehicleSelect.disabled = true;
                            setVehicleSelectOptions([]);
                            if (bookingVehicleStatus) {
                                bookingVehicleStatus.textContent = 'Select a branch to load available vehicles.';
                            }
                            updateCreateButtonState();
                            return;
                        }

                        if (!startDate.value || !startHour.value || !startMinute.value || !endDate.value || !endHour.value || !endMinute.value) {
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
                            branch_id: branchId,
                            planned_start_date: startDate.value,
                            planned_start_hour: startHour.value,
                            planned_start_minute: startMinute.value,
                            planned_end_date: endDate.value,
                            planned_end_hour: endHour.value,
                            planned_end_minute: endMinute.value,
                        });

                        try {
                            const res = await fetch(`/app/sharpfleet/bookings/available-vehicles?${params.toString()}`,
                                {
                                    credentials: 'same-origin',
                                    headers: { 'Accept': 'application/json' },
                                }
                            );
                            if (!res.ok) {
                                const msg = await getResponseErrorMessage(res);
                                bookingVehicleSelect.disabled = true;
                                setVehicleSelectOptions([]);
                                if (bookingVehicleStatus) {
                                    bookingVehicleStatus.textContent = msg || 'Could not load vehicles for that time window.';
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

                    [startDate, startHour, startMinute, endDate, endHour, endMinute].forEach(el => {
                        if (el) el.addEventListener('change', loadAvailableVehicles);
                    });

                    if (branchSelect) {
                        branchSelect.addEventListener('change', loadAvailableVehicles);
                    }

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
                                    @php($rowTz = isset($b->timezone) && trim((string)$b->timezone) !== '' ? (string)$b->timezone : $companyTimezone)
                                    <td>{{ \Carbon\Carbon::parse($b->planned_start)->timezone($rowTz)->format('d/m/Y H:i') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($b->planned_end)->timezone($rowTz)->format('d/m/Y H:i') }}</td>
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
