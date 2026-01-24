@extends('layouts.sharpfleet')

@section('title', 'Bookings')

@section('sharpfleet-content')

@php
    use App\Services\SharpFleet\CompanySettingsService;
    use App\Support\SharpFleet\Roles;

    $user = session('sharpfleet.user');
    $sfRole = Roles::normalize($user['role'] ?? null);
    $sfIsDriver = $sfRole === Roles::DRIVER;
    $sfUserId = (int) ($user['id'] ?? 0);
    $sfUserName = trim((string)($user['first_name'] ?? '') . ' ' . (string)($user['last_name'] ?? ''));
    $sfCanEditBookings = in_array($sfRole, [Roles::COMPANY_ADMIN, Roles::BOOKING_ADMIN], true);

    $settingsService = new CompanySettingsService((int) $user['organisation_id']);
    $companyTimezone = $defaultTimezone ?? $settingsService->timezone();
    $today = \Carbon\Carbon::now($companyTimezone)->format('Y-m-d');

    $branchesEnabled = (bool) ($branchesEnabled ?? false);
    $branches = $branches ?? collect();

    $vehiclesForJs = collect($vehicles ?? collect())->map(function ($v) {
        return [
            'id' => (int) ($v->id ?? 0),
            'name' => (string) ($v->name ?? ''),
            'registration_number' => (string) ($v->registration_number ?? ''),
            'branch_id' => isset($v->branch_id) ? (int) $v->branch_id : null,
        ];
    })->values();

    $driversForJs = collect($drivers ?? collect())->map(function ($d) {
        return [
            'id' => (int) ($d->id ?? 0),
            'name' => trim((string) ($d->first_name ?? '') . ' ' . (string) ($d->last_name ?? '')),
        ];
    })->values();

    $branchesForJs = collect($branches)->map(function ($b) {
        return [
            'id' => (int) ($b->id ?? 0),
            'name' => (string) ($b->name ?? ''),
        ];
    })->values();
@endphp

<div class="container">
    <div class="page-header">
        <div class="flex-between">
            <div>
                <h1 class="page-title">Bookings</h1>
                <p class="page-description">Manage upcoming bookings. A vehicle cannot be used during a booked window except by the booking owner.</p>
            </div>
        </div>
    </div>

    <div id="sfOfflineNotice" class="alert alert-warning" style="display:none;">
        You’re currently offline. Availability checks may fail until your connection is restored.
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
            <div class="d-flex justify-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="btn-group" role="group" aria-label="Calendar view">
                        <button type="button" class="btn-sf-navy btn-sm" id="sfBkViewDay">Day</button>
                        <button type="button" class="btn-sf-navy btn-sm" id="sfBkViewWeek">Week</button>
                        <button type="button" class="btn-sf-navy btn-sm" id="sfBkViewMonth">Month</button>
                    </div>

                    @if($branchesEnabled && $branches->count() > 1)
                        <div class="d-flex align-items-center gap-2">
                            <label class="text-muted small mb-0" for="sfBkBranch">Branch</label>
                            <select id="sfBkBranch" class="form-control" style="min-width:220px;">
                                <option value="">All accessible</option>
                                @foreach($branches as $br)
                                    <option value="{{ $br->id }}">{{ $br->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2">
                    @if($sfCanEditBookings)
                        <button type="button" class="btn-sf-navy btn-sm" id="sfBkCreateBtn">Create Booking</button>
                    @endif
                    <button type="button" class="btn-sf-navy btn-sm" id="sfBkPrev">Prev</button>
                    <button type="button" class="btn-sf-navy btn-sm" id="sfBkToday">Today</button>
                    <button type="button" class="btn-sf-navy btn-sm" id="sfBkNext">Next</button>
                </div>
            </div>
            <div class="text-muted small mt-2" id="sfBkRangeLabel"></div>
        </div>

        <div class="card-body">
            @if(!$bookingsTableExists)
                <p class="text-muted fst-italic">Bookings are unavailable until the database table is created.</p>
            @else
                <div id="sfBkLoading" class="text-muted small" style="display:none;">Loading…</div>

                <div id="sfBkCalendar"></div>

                <div class="text-muted small mt-2">
                    Click an empty slot to create a booking. Click an existing booking to {{ $sfCanEditBookings ? 'view/edit' : 'view details' }}.
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Create Booking Modal --}}
<div id="sfBkCreateModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5);">
    <div class="card" style="max-width:760px; margin:3vh auto;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                    <h3 class="mb-1">Create booking</h3>
                    <p class="text-muted mb-0">Driver, vehicle, date and time are required.</p>
                </div>
                <button type="button"
                    class="sf-modal-close"
                        id="sfBkCreateClose"
                        aria-label="Close"
                        title="Close"
                    style="">&times;</button>
            </div>

            <div class="mt-2"></div>

            <form id="sfBkCreateForm" method="POST" action="{{ url('/app/sharpfleet/admin/bookings') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">Driver</label>
                    @if($sfIsDriver)
                        <input type="hidden" id="sfBkCreateDriver" name="user_id" value="{{ $sfUserId }}">
                        <input type="text" class="form-control" value="{{ $sfUserName ?: 'Driver' }}" readonly>
                    @else
                        <select id="sfBkCreateDriver" name="user_id" class="form-control" required>
                            <option value="">— Select driver —</option>
                            @foreach($drivers as $d)
                                <option value="{{ $d->id }}" {{ (int)$d->id === $sfUserId ? 'selected' : '' }}>
                                    {{ $d->first_name }} {{ $d->last_name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                @if($branchesEnabled && $branches->count() > 1)
                    <div class="form-group">
                        <label class="form-label">Branch</label>
                        <select id="sfBkCreateBranch" name="branch_id" class="form-control">
                            <option value="">— Auto —</option>
                            @foreach($branches as $br)
                                <option value="{{ $br->id }}">{{ $br->name }}</option>
                            @endforeach
                        </select>
                        <div class="hint-text">If left blank, the vehicle’s branch will be used when supported.</div>
                    </div>
                @elseif($branchesEnabled && $branches->count() === 1)
                    <input type="hidden" id="sfBkCreateBranch" name="branch_id" value="{{ (int) ($branches->first()->id ?? 0) }}">
                @endif

                <div class="sf-bk-section-label">Booking window</div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Start date</label>
                        <input id="sfBkCreateStartDate" type="text" name="planned_start_date" class="form-control sf-date" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Start time</label>
                        <div class="sf-time-and-reminder">
                            <div class="sf-time-row">
                                <select id="sfBkCreateStartHour" name="planned_start_hour" class="form-control sf-time-hh" required>
                                    <option value="">HH</option>
                                    @for($h = 0; $h <= 23; $h++)
                                        @php($hh = str_pad((string)$h, 2, '0', STR_PAD_LEFT))
                                        <option value="{{ $hh }}">{{ $hh }}</option>
                                    @endfor
                                </select>
                                <span class="sf-time-sep">:</span>
                                <select id="sfBkCreateStartMinute" name="planned_start_minute" class="form-control sf-time-mm" required>
                                    <option value="">MM</option>
                                    @for($m = 0; $m <= 55; $m += 5)
                                        @php($mm = str_pad((string)$m, 2, '0', STR_PAD_LEFT))
                                        <option value="{{ $mm }}">{{ $mm }}</option>
                                    @endfor
                                </select>
                            </div>

                            <label class="d-flex align-items-center gap-2 sf-reminder-inline" style="cursor:pointer; margin:0;">
                                <input id="sfBkCreateRemindMe" type="checkbox" name="remind_me" value="1">
                                <span class="text-muted">Reminder (1 hour before)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">End date</label>
                        <input id="sfBkCreateEndDate" type="text" name="planned_end_date" class="form-control sf-date" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">End time</label>
                        <div class="sf-time-row">
                            <select id="sfBkCreateEndHour" name="planned_end_hour" class="form-control sf-time-hh" required>
                                <option value="">HH</option>
                                @for($h = 0; $h <= 23; $h++)
                                    @php($hh = str_pad((string)$h, 2, '0', STR_PAD_LEFT))
                                    <option value="{{ $hh }}">{{ $hh }}</option>
                                @endfor
                            </select>
                            <span class="sf-time-sep">:</span>
                            <select id="sfBkCreateEndMinute" name="planned_end_minute" class="form-control sf-time-mm" required>
                                <option value="">MM</option>
                                @for($m = 0; $m <= 55; $m += 5)
                                    @php($mm = str_pad((string)$m, 2, '0', STR_PAD_LEFT))
                                    <option value="{{ $mm }}">{{ $mm }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <div id="sfBkCreateVehicleSection" style="display:none;">
                    <div class="form-group">
                        <label class="form-label">Available vehicle</label>
                        <div id="sfBkCreateVehicleStatus" class="hint-text">Select a future time to see available vehicles.</div>
                        <select id="sfBkCreateVehicle" name="vehicle_id" class="form-control" required disabled>
                            <option value="">— Select vehicle —</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Customer / Client (optional)</label>
                    @if($customersTableExists && $customers->count() > 0)
                        <select id="sfBkCreateCustomer" name="customer_id" class="form-control">
                            <option value="">— Select from list —</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <div class="hint-text">If the customer isn’t in the list, type a name below.</div>
                    @endif
                    <input id="sfBkCreateCustomerName" type="text" name="customer_name" class="form-control mt-2" maxlength="150" placeholder="Or enter customer name">
                </div>

                <div class="form-group">
                    <label class="form-label text-muted">Notes (optional)</label>
                    <textarea id="sfBkCreateNotes" name="notes" class="form-control sf-bk-notes" rows="2" placeholder="Optional"></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn-sf-navy btn-sm" id="sfBkCreateCancelBtn">Cancel</button>
                    <button id="sfBkCreateSubmit" type="submit" class="btn-sf-navy btn-sm" disabled>Create Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Booking Modal --}}
<div id="sfBkEditModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5);">
    <div class="card" style="max-width:760px; margin:3vh auto;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                    <h3 class="mb-1" id="sfBkEditTitle">Edit booking</h3>
                    <p class="text-muted mb-0" id="sfBkEditSubtitle"></p>
                </div>
                <button type="button"
                    class="sf-modal-close btn-sf-navy"
                        id="sfBkEditClose"
                        aria-label="Close"
                        title="Close"
                    style="">&times;</button>
            </div>

            <div class="alert alert-info" id="sfBkEditCreatedByNotice" style="display:none; margin-top:12px;"></div>

            <div class="mt-3"></div>

            <form id="sfBkEditForm" method="POST" action="">
                @csrf

                <input type="hidden" id="sfBkEditId" value="">

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Driver</label>
                        <select id="sfBkEditDriver" name="user_id" class="form-control" required>
                            <option value="">— Select driver —</option>
                            @foreach($drivers as $d)
                                <option value="{{ $d->id }}">{{ $d->first_name }} {{ $d->last_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Vehicle</label>
                        <select id="sfBkEditVehicle" name="vehicle_id" class="form-control" required>
                            <option value="">— Select vehicle —</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->registration_number }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if($branchesEnabled && $branches->count() > 1)
                    <div class="form-group">
                        <label class="form-label">Branch</label>
                        <select id="sfBkEditBranch" name="branch_id" class="form-control">
                            <option value="">— Auto —</option>
                            @foreach($branches as $br)
                                <option value="{{ $br->id }}">{{ $br->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif($branchesEnabled && $branches->count() === 1)
                    <input type="hidden" id="sfBkEditBranch" name="branch_id" value="{{ (int) ($branches->first()->id ?? 0) }}">
                @endif

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Start date</label>
                        <input id="sfBkEditStartDate" type="text" name="planned_start_date" class="form-control sf-date" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start time</label>
                        <div class="sf-time-row">
                            <select id="sfBkEditStartHour" name="planned_start_hour" class="form-control sf-time-hh" required>
                                <option value="">HH</option>
                                @for($h = 0; $h <= 23; $h++)
                                    @php($hh = str_pad((string)$h, 2, '0', STR_PAD_LEFT))
                                    <option value="{{ $hh }}">{{ $hh }}</option>
                                @endfor
                            </select>
                            <span class="sf-time-sep">:</span>
                            <select id="sfBkEditStartMinute" name="planned_start_minute" class="form-control sf-time-mm" required>
                                <option value="">MM</option>
                                @for($m = 0; $m <= 59; $m++)
                                    @php($mm = str_pad((string)$m, 2, '0', STR_PAD_LEFT))
                                    <option value="{{ $mm }}">{{ $mm }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">End date</label>
                        <input id="sfBkEditEndDate" type="text" name="planned_end_date" class="form-control sf-date" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End time</label>
                        <div class="sf-time-row">
                            <select id="sfBkEditEndHour" name="planned_end_hour" class="form-control sf-time-hh" required>
                                <option value="">HH</option>
                                @for($h = 0; $h <= 23; $h++)
                                    @php($hh = str_pad((string)$h, 2, '0', STR_PAD_LEFT))
                                    <option value="{{ $hh }}">{{ $hh }}</option>
                                @endfor
                            </select>
                            <span class="sf-time-sep">:</span>
                            <select id="sfBkEditEndMinute" name="planned_end_minute" class="form-control sf-time-mm" required>
                                <option value="">MM</option>
                                @for($m = 0; $m <= 59; $m++)
                                    @php($mm = str_pad((string)$m, 2, '0', STR_PAD_LEFT))
                                    <option value="{{ $mm }}">{{ $mm }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="d-flex align-items-center gap-2" style="cursor:pointer;">
                        <input id="sfBkEditRemindMe" type="checkbox" name="remind_me" value="1">
                        <span>Reminder (1 hour before start)</span>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label">Customer / Client (optional)</label>
                    @if($customersTableExists && $customers->count() > 0)
                        <select id="sfBkEditCustomer" name="customer_id" class="form-control">
                            <option value="">— Select from list —</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <input id="sfBkEditCustomerName" type="text" name="customer_name" class="form-control mt-2" maxlength="150" placeholder="Or enter customer name">
                </div>

                <div class="form-group">
                    <label class="form-label">Notes (optional)</label>
                    <textarea id="sfBkEditNotes" name="notes" class="form-control" rows="3"></textarea>
                </div>

                <div class="d-flex justify-content-between gap-2">
                    <button type="button" class="btn-sf-navy btn-sm" id="sfBkEditCancelBooking">Cancel booking</button>
                    <div class="d-flex gap-2" id="sfBkEditActions">
                        <button type="button" class="btn-sf-navy btn-sm" id="sfBkEditCloseBtn">Close</button>
                        <button type="submit" class="btn-sf-navy btn-sm" id="sfBkEditSubmit">Save changes</button>
                    </div>
                </div>
            </form>

            <form id="sfBkCancelForm" method="POST" action="" style="display:none;">
                @csrf
            </form>
        </div>
    </div>
</div>

@if($bookingsTableExists)

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ secure_asset('css/sharpfleet/bookings.css') }}?v={{ @filemtime(public_path('css/sharpfleet/bookings.css')) ?: time() }}">
@endpush

@push('scripts')
    <script>
        window.SharpFleetBookingsConfig = {
            timezone: @json($companyTimezone),
            today: @json($today),
            currentUserId: @json((int) ($user['id'] ?? 0)),
            canEditBookings: @json((bool) $sfCanEditBookings),
            vehicles: @json($vehiclesForJs),
            drivers: @json($driversForJs),
            branchesEnabled: @json((bool) $branchesEnabled),
            branches: @json($branchesForJs),
            customersEnabled: @json((bool) ($customersTableExists ?? false)),
        };
    </script>
    <script src="https://unpkg.com/flatpickr"></script>
    <script>
        (function () {
            if (typeof flatpickr === 'undefined') return;
            flatpickr('.sf-date', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                allowInput: true,
                minDate: @json($today),
            });
        })();
    </script>
    <script src="{{ secure_asset('js/sharpfleet/bookings.js') }}?v={{ @filemtime(public_path('js/sharpfleet/bookings.js')) ?: time() }}"></script>
@endpush
@endif

@endsection
