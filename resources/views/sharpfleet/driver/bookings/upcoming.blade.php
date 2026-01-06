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

    $editBooking = $editBooking ?? null;
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
            <h2 class="card-title">{{ $editBooking ? 'Edit Booking' : 'Create Booking' }}</h2>
            <p class="card-subtitle">Date and time are required. Customer/client is optional.</p>
        </div>
        <div class="card-body">
            @if(!$bookingsTableExists)
                <p class="text-muted fst-italic">Bookings are unavailable until the database table is created.</p>
            @else
                <form method="POST" action="{{ $editBooking ? url('/app/sharpfleet/bookings/' . (int)($editBooking['id'] ?? 0)) : url('/app/sharpfleet/bookings') }}">
                    @csrf

                    @if($branchesEnabled && $branches->count() > 1)
                        <div class="form-group">
                            <label class="form-label">Branch</label>
                            <select id="bookingBranchSelect" name="branch_id" class="form-control" required>
                                <option value="">— Select branch —</option>
                                @foreach($branches as $br)
                                    @php($selectedBranch = old('branch_id') !== null ? old('branch_id') : ($editBooking ? ($editBooking['branch_id'] ?? '') : ''))
                                    <option value="{{ $br->id }}" {{ (string)$selectedBranch === (string)$br->id ? 'selected' : '' }}>
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
                            @php($startDateVal = old('planned_start_date') !== null ? old('planned_start_date') : ($editBooking ? ($editBooking['planned_start_date'] ?? '') : ''))
                            <input type="date" name="planned_start_date" class="form-control" required min="{{ $today }}" value="{{ $startDateVal }}">
                            @error('planned_start_date')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Start time</label>
                            @php($startTimeVal = old('planned_start_time') !== null ? old('planned_start_time') : ($editBooking ? ($editBooking['planned_start_time'] ?? '') : ''))
                            <input type="time" name="planned_start_time" class="form-control" required value="{{ $startTimeVal }}" step="60">
                            @error('planned_start_time')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
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
                            @php($endDateVal = old('planned_end_date') !== null ? old('planned_end_date') : ($editBooking ? ($editBooking['planned_end_date'] ?? '') : ''))
                            <input type="date" name="planned_end_date" class="form-control" required min="{{ $today }}" value="{{ $endDateVal }}">
                            @error('planned_end_date')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">End time</label>
                            @php($endTimeVal = old('planned_end_time') !== null ? old('planned_end_time') : ($editBooking ? ($editBooking['planned_end_time'] ?? '') : ''))
                            <input type="time" name="planned_end_time" class="form-control" required value="{{ $endTimeVal }}" step="60">
                            @error('planned_end_time')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
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
                        @php($selectedVehicle = old('vehicle_id') !== null ? old('vehicle_id') : ($editBooking ? ($editBooking['vehicle_id'] ?? '') : ''))
                        <select id="bookingVehicleSelect" name="vehicle_id" class="form-control" required disabled data-selected-vehicle-id="{{ (string)$selectedVehicle }}">
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
                                    @php($selectedCustomer = old('customer_id') !== null ? old('customer_id') : ($editBooking ? ($editBooking['customer_id'] ?? '') : ''))
                                    <option value="{{ $c->id }}" {{ (string)$selectedCustomer === (string)$c->id ? 'selected' : '' }} data-branch-id="{{ property_exists($c, 'branch_id') ? (string)($c->branch_id ?? '') : '' }}">
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="hint-text">If the customer isn’t in the list, type a name below.</div>
                        @endif

                        @php($customerNameVal = old('customer_name') !== null ? old('customer_name') : ($editBooking ? ($editBooking['customer_name'] ?? '') : ''))
                        <input id="bookingCustomerNameInput" type="text" name="customer_name" class="form-control mt-2" maxlength="150" placeholder="Or enter customer name" value="{{ $customerNameVal }}">
                        @error('customer_name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes (optional)</label>
                        @php($notesVal = old('notes') !== null ? old('notes') : ($editBooking ? ($editBooking['notes'] ?? '') : ''))
                        <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes for admin/driver">{{ $notesVal }}</textarea>
                        @error('notes')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <button id="createBookingBtn" type="submit" class="btn btn-primary btn-full" disabled>{{ $editBooking ? 'Save Changes' : 'Create Booking' }}</button>
                    @if($editBooking)
                        <div class="mt-2">
                            <a href="{{ url('/app/sharpfleet/bookings') }}" class="btn btn-secondary btn-full">Cancel Edit</a>
                        </div>
                    @endif
                </form>

                <script>
                    const startDate = document.querySelector('input[name="planned_start_date"]');
                    const startTime = document.querySelector('input[name="planned_start_time"]');
                    const endDate = document.querySelector('input[name="planned_end_date"]');
                    const endTime = document.querySelector('input[name="planned_end_time"]');

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

                    function applyCustomerBranchFilter() {
                        const bookingCustomerSelect = document.getElementById('bookingCustomerSelect');
                        if (!bookingCustomerSelect || !branchSelect) return;

                        const selectedBranchId = branchSelect.value;
                        const options = Array.from(bookingCustomerSelect.querySelectorAll('option'));
                        options.forEach((opt, idx) => {
                            if (idx === 0) {
                                opt.hidden = false;
                                return;
                            }
                            const optBranch = opt.getAttribute('data-branch-id') || '';
                            // If customers table doesn't have branch_id (attribute empty), do not hide.
                            if (!selectedBranchId || !optBranch) {
                                opt.hidden = false;
                                return;
                            }
                            opt.hidden = optBranch !== selectedBranchId;
                        });

                        // If current selection became hidden, clear it.
                        const selectedOpt = bookingCustomerSelect.selectedOptions && bookingCustomerSelect.selectedOptions[0];
                        if (selectedOpt && selectedOpt.hidden) {
                            bookingCustomerSelect.value = '';
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
                        if (!bookingVehicleSelect || !startDate || !startTime || !endDate || !endTime) return;

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
                            branch_id: branchId,
                            planned_start_date: startDate.value,
                            planned_start_time: startTime.value,
                            planned_end_date: endDate.value,
                            planned_end_time: endTime.value,
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

                            const selectedVehicleId = bookingVehicleSelect.getAttribute('data-selected-vehicle-id');
                            if (selectedVehicleId) {
                                bookingVehicleSelect.value = selectedVehicleId;
                            }

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

                    if (branchSelect) {
                        branchSelect.addEventListener('change', loadAvailableVehicles);
                        branchSelect.addEventListener('change', applyCustomerBranchFilter);
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
                    applyCustomerBranchFilter();
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
                                            <div class="grid grid-2">
                                                <a class="btn btn-secondary" href="{{ url('/app/sharpfleet/bookings?edit=' . (int)$b->id) }}">Edit</a>
                                                <form method="POST" action="{{ url('/app/sharpfleet/bookings/' . $b->id . '/cancel') }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-secondary">Cancel</button>
                                                </form>
                                            </div>
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
