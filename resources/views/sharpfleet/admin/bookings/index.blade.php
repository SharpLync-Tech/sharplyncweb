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

    $vehiclesForJs = collect($vehicles ?? collect())->map(function ($v) {
        return [
            'id' => (int) ($v->id ?? 0),
            'name' => (string) ($v->name ?? ''),
            'registration_number' => (string) ($v->registration_number ?? ''),
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
            <div class="d-flex justify-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="btn-group" role="group" aria-label="Calendar view">
                        <button type="button" class="btn btn-secondary" id="sfBkViewDay">Day</button>
                        <button type="button" class="btn btn-secondary" id="sfBkViewWeek">Week</button>
                        <button type="button" class="btn btn-secondary" id="sfBkViewMonth">Month</button>
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
                    <button type="button" class="btn btn-secondary" id="sfBkPrev">Prev</button>
                    <button type="button" class="btn btn-secondary" id="sfBkToday">Today</button>
                    <button type="button" class="btn btn-secondary" id="sfBkNext">Next</button>
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
                    Click an empty slot to create a booking. Click an existing booking to view/edit.
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Create Booking Modal --}}
<div id="sfBkCreateModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5);">
    <div class="card" style="max-width:760px; margin:6vh auto;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                    <h3 class="mb-1">Create booking</h3>
                    <p class="text-muted mb-0">Driver, vehicle, date and time are required.</p>
                </div>
                <button type="button"
                        class="btn btn-secondary btn-sm"
                        id="sfBkCreateClose"
                        aria-label="Close"
                        title="Close"
                        style="width:38px; height:38px; display:flex; align-items:center; justify-content:center; padding:0; font-size:22px; line-height:1;">&times;</button>
            </div>

            <div class="mt-3"></div>

            <form id="sfBkCreateForm" method="POST" action="{{ url('/app/sharpfleet/admin/bookings') }}">
                @csrf

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Driver</label>
                        <select id="sfBkCreateDriver" name="user_id" class="form-control" required>
                            <option value="">— Select driver —</option>
                            @foreach($drivers as $d)
                                <option value="{{ $d->id }}">{{ $d->first_name }} {{ $d->last_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Vehicle (available only)</label>
                        <div id="sfBkCreateVehicleStatus" class="hint-text">Select start/end date & time to load available vehicles.</div>
                        <select id="sfBkCreateVehicle" name="vehicle_id" class="form-control" required disabled>
                            <option value="">— Select vehicle —</option>
                        </select>
                    </div>
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

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Start date</label>
                        <input id="sfBkCreateStartDate" type="date" name="planned_start_date" class="form-control" required min="{{ $today }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start time</label>
                        <div class="grid grid-2">
                            <select id="sfBkCreateStartHour" name="planned_start_hour" class="form-control" required>
                                <option value="">HH</option>
                                @for($h = 0; $h <= 23; $h++)
                                    @php($hh = str_pad((string)$h, 2, '0', STR_PAD_LEFT))
                                    <option value="{{ $hh }}">{{ $hh }}</option>
                                @endfor
                            </select>
                            <select id="sfBkCreateStartMinute" name="planned_start_minute" class="form-control" required>
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
                        <input id="sfBkCreateEndDate" type="date" name="planned_end_date" class="form-control" required min="{{ $today }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End time</label>
                        <div class="grid grid-2">
                            <select id="sfBkCreateEndHour" name="planned_end_hour" class="form-control" required>
                                <option value="">HH</option>
                                @for($h = 0; $h <= 23; $h++)
                                    @php($hh = str_pad((string)$h, 2, '0', STR_PAD_LEFT))
                                    <option value="{{ $hh }}">{{ $hh }}</option>
                                @endfor
                            </select>
                            <select id="sfBkCreateEndMinute" name="planned_end_minute" class="form-control" required>
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
                        <input id="sfBkCreateRemindMe" type="checkbox" name="remind_me" value="1">
                        <span>Reminder (1 hour before start)</span>
                    </label>
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
                    <label class="form-label">Notes (optional)</label>
                    <textarea id="sfBkCreateNotes" name="notes" class="form-control" rows="3"></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" id="sfBkCreateCancelBtn">Cancel</button>
                    <button id="sfBkCreateSubmit" type="submit" class="btn btn-primary" disabled>Create Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Booking Modal --}}
<div id="sfBkEditModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5);">
    <div class="card" style="max-width:760px; margin:6vh auto;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                    <h3 class="mb-1">Edit booking</h3>
                    <p class="text-muted mb-0" id="sfBkEditSubtitle"></p>
                </div>
                <button type="button"
                        class="btn btn-secondary btn-sm"
                        id="sfBkEditClose"
                        aria-label="Close"
                        title="Close"
                        style="width:38px; height:38px; display:flex; align-items:center; justify-content:center; padding:0; font-size:22px; line-height:1;">&times;</button>
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
                        <input id="sfBkEditStartDate" type="date" name="planned_start_date" class="form-control" required min="{{ $today }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start time</label>
                        <div class="grid grid-2">
                            <select id="sfBkEditStartHour" name="planned_start_hour" class="form-control" required>
                                <option value="">HH</option>
                                @for($h = 0; $h <= 23; $h++)
                                    @php($hh = str_pad((string)$h, 2, '0', STR_PAD_LEFT))
                                    <option value="{{ $hh }}">{{ $hh }}</option>
                                @endfor
                            </select>
                            <select id="sfBkEditStartMinute" name="planned_start_minute" class="form-control" required>
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
                        <input id="sfBkEditEndDate" type="date" name="planned_end_date" class="form-control" required min="{{ $today }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End time</label>
                        <div class="grid grid-2">
                            <select id="sfBkEditEndHour" name="planned_end_hour" class="form-control" required>
                                <option value="">HH</option>
                                @for($h = 0; $h <= 23; $h++)
                                    @php($hh = str_pad((string)$h, 2, '0', STR_PAD_LEFT))
                                    <option value="{{ $hh }}">{{ $hh }}</option>
                                @endfor
                            </select>
                            <select id="sfBkEditEndMinute" name="planned_end_minute" class="form-control" required>
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
                    <button type="button" class="btn btn-secondary" id="sfBkEditCancelBooking">Cancel booking</button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" id="sfBkEditCloseBtn">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
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

<style>
    /* SharpFleet bookings calendar (locked NAVY/TEAL spec) */
    .sf-bk-scroll{overflow:auto;max-height:70vh;border:1px solid var(--sl-border,#e5e7eb);border-radius:12px;background:#fff}
    .sf-bk-header{position:sticky;top:0;z-index:5;background:#fff}
    .sf-bk-left{width:240px;flex:0 0 240px;position:sticky;left:0;z-index:6;background:#fff}
    .sf-bk-left-inner{padding:10px 12px;color:var(--sl-navy)}
    .sf-bk-time-header{position:relative;height:54px;min-height:54px}
    .sf-bk-time-label{position:absolute;top:10px;font-size:12px;color:var(--sl-navy);white-space:nowrap}
    .sf-bk-row{display:flex}
    .sf-bk-lane{position:relative;height:54px}
    .sf-bk-lane-bg{background:#fff}
    .sf-bk-slot-hover{position:absolute;top:6px;height:42px;border:2px solid var(--sl-teal);border-radius:10px;background:rgba(44,191,174,.06);pointer-events:none;display:none}
    .sf-bk-block{position:absolute;top:6px;height:42px;border-radius:10px;background:var(--sl-navy);color:#fff;padding:6px 10px;cursor:pointer;overflow:hidden;box-shadow:0 8px 18px rgba(10,42,77,.18)}
    .sf-bk-block::before{content:"";position:absolute;left:0;top:0;bottom:0;width:5px;background:var(--sl-teal)}
    .sf-bk-block:hover{outline:2px solid var(--sl-teal);filter:brightness(1.05)}
    .sf-bk-block-title{font-size:12px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding-left:6px}
    .sf-bk-block-time{font-size:12px;opacity:.85;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding-left:6px}

    .sf-bk-month-ind{margin-top:8px;display:flex;align-items:center;gap:8px}
    .sf-bk-month-bar{flex:1;height:8px;border-radius:999px;background:rgba(10,42,77,.08);overflow:hidden}
    .sf-bk-month-bar > span{display:block;height:100%;background:var(--sl-teal)}
    .sf-bk-month-txt{font-size:12px;color:var(--sl-navy);white-space:nowrap}
</style>

<script>
    (function () {
        const sf = {
            timezone: @json($companyTimezone),
            today: @json($today),
            currentUserId: @json((int) ($user['id'] ?? 0)),
            vehicles: @json($vehiclesForJs),
            drivers: @json($driversForJs),
            branchesEnabled: @json((bool) $branchesEnabled),
            branches: @json($branchesForJs),
            customersEnabled: @json((bool) ($customersTableExists ?? false)),
        };

        const els = {
            viewDay: document.getElementById('sfBkViewDay'),
            viewWeek: document.getElementById('sfBkViewWeek'),
            viewMonth: document.getElementById('sfBkViewMonth'),
            prev: document.getElementById('sfBkPrev'),
            next: document.getElementById('sfBkNext'),
            today: document.getElementById('sfBkToday'),
            range: document.getElementById('sfBkRangeLabel'),
            cal: document.getElementById('sfBkCalendar'),
            loading: document.getElementById('sfBkLoading'),
            branch: document.getElementById('sfBkBranch'),

            createModal: document.getElementById('sfBkCreateModal'),
            createClose: document.getElementById('sfBkCreateClose'),
            createCancelBtn: document.getElementById('sfBkCreateCancelBtn'),
            createForm: document.getElementById('sfBkCreateForm'),
            createSubmit: document.getElementById('sfBkCreateSubmit'),
            createBranch: document.getElementById('sfBkCreateBranch'),
            createDriver: document.getElementById('sfBkCreateDriver'),
            createVehicle: document.getElementById('sfBkCreateVehicle'),
            createVehicleStatus: document.getElementById('sfBkCreateVehicleStatus'),
            createStartDate: document.getElementById('sfBkCreateStartDate'),
            createStartHour: document.getElementById('sfBkCreateStartHour'),
            createStartMinute: document.getElementById('sfBkCreateStartMinute'),
            createEndDate: document.getElementById('sfBkCreateEndDate'),
            createEndHour: document.getElementById('sfBkCreateEndHour'),
            createEndMinute: document.getElementById('sfBkCreateEndMinute'),
            createCustomer: document.getElementById('sfBkCreateCustomer'),
            createCustomerName: document.getElementById('sfBkCreateCustomerName'),

            editModal: document.getElementById('sfBkEditModal'),
            editClose: document.getElementById('sfBkEditClose'),
            editCloseBtn: document.getElementById('sfBkEditCloseBtn'),
            editSubtitle: document.getElementById('sfBkEditSubtitle'),
            editCreatedByNotice: document.getElementById('sfBkEditCreatedByNotice'),
            editForm: document.getElementById('sfBkEditForm'),
            editId: document.getElementById('sfBkEditId'),
            editBranch: document.getElementById('sfBkEditBranch'),
            editDriver: document.getElementById('sfBkEditDriver'),
            editVehicle: document.getElementById('sfBkEditVehicle'),
            editStartDate: document.getElementById('sfBkEditStartDate'),
            editStartHour: document.getElementById('sfBkEditStartHour'),
            editStartMinute: document.getElementById('sfBkEditStartMinute'),
            editEndDate: document.getElementById('sfBkEditEndDate'),
            editEndHour: document.getElementById('sfBkEditEndHour'),
            editEndMinute: document.getElementById('sfBkEditEndMinute'),
            editRemindMe: document.getElementById('sfBkEditRemindMe'),
            editCustomer: document.getElementById('sfBkEditCustomer'),
            editCustomerName: document.getElementById('sfBkEditCustomerName'),
            editNotes: document.getElementById('sfBkEditNotes'),
            editCancelBooking: document.getElementById('sfBkEditCancelBooking'),
            cancelForm: document.getElementById('sfBkCancelForm'),
        };

        function show(el) { if (el) el.style.display = 'block'; }
        function hide(el) { if (el) el.style.display = 'none'; }

        function pad2(n) { return String(n).padStart(2, '0'); }

        function parseYmd(ymd) {
            const [y, m, d] = String(ymd).split('-').map(Number);
            return Date.UTC(y, (m || 1) - 1, d || 1, 0, 0, 0, 0);
        }

        function parseYmdHi(s) {
            // Treat "YYYY-MM-DD HH:MM" as a floating local time; use UTC parsing for stable diffs.
            const parts = String(s || '').trim().split(' ');
            if (parts.length !== 2) return NaN;
            const [y, m, d] = parts[0].split('-').map(Number);
            const [hh, mm] = parts[1].split(':').map(Number);
            return Date.UTC(y, (m || 1) - 1, d || 1, hh || 0, mm || 0, 0, 0);
        }

        function formatYmd(ms) {
            const dt = new Date(ms);
            return dt.getUTCFullYear() + '-' + pad2(dt.getUTCMonth() + 1) + '-' + pad2(dt.getUTCDate());
        }

        function formatDmyHi(ms) {
            const dt = new Date(ms);
            return pad2(dt.getUTCDate()) + '/' + pad2(dt.getUTCMonth() + 1) + '/' + dt.getUTCFullYear() + ' ' + pad2(dt.getUTCHours()) + ':' + pad2(dt.getUTCMinutes());
        }

        function startOfWeekMonday(ms) {
            const dt = new Date(ms);
            const day = dt.getUTCDay(); // 0 Sun
            const diff = (day + 6) % 7; // 0 if Mon
            return ms - diff * 86400000;
        }

        function lastDayOfMonth(ms) {
            const dt = new Date(ms);
            const y = dt.getUTCFullYear();
            const m = dt.getUTCMonth();
            // day 0 of next month = last day of this month
            return Date.UTC(y, m + 1, 0, 0, 0, 0, 0);
        }

        function firstDayOfMonth(ms) {
            const dt = new Date(ms);
            return Date.UTC(dt.getUTCFullYear(), dt.getUTCMonth(), 1, 0, 0, 0, 0);
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
                // ignore
            }
            return null;
        }

        const state = {
            view: 'week',
            anchorMs: parseYmd(sf.today),
            bookings: [],
            rangeStartMs: null,
            rangeEndMs: null,
        };

        function setActiveViewButtons() {
            const map = {
                day: els.viewDay,
                week: els.viewWeek,
                month: els.viewMonth,
            };
            Object.entries(map).forEach(([k, btn]) => {
                if (!btn) return;
                btn.className = (k === state.view) ? 'btn btn-primary' : 'btn btn-secondary';
            });
        }

        function computeRange() {
            if (state.view === 'day') {
                const start = parseYmd(formatYmd(state.anchorMs));
                state.rangeStartMs = start;
                state.rangeEndMs = start + 86400000;
                return;
            }

            if (state.view === 'week') {
                const start = startOfWeekMonday(state.anchorMs);
                state.rangeStartMs = start;
                state.rangeEndMs = start + 7 * 86400000;
                return;
            }

            // month
            const start = firstDayOfMonth(state.anchorMs);
            const endDay = lastDayOfMonth(state.anchorMs);
            state.rangeStartMs = start;
            state.rangeEndMs = endDay + 86400000;
        }

        function setRangeLabel() {
            if (!els.range) return;
            if (state.view === 'day') {
                els.range.textContent = 'Day: ' + formatYmd(state.rangeStartMs);
                return;
            }
            if (state.view === 'week') {
                const endMs = state.rangeEndMs - 60000;
                els.range.textContent = 'Week: ' + formatYmd(state.rangeStartMs) + ' → ' + formatYmd(endMs);
                return;
            }
            const dt = new Date(state.anchorMs);
            const month = dt.toLocaleString('en-GB', { month: 'long', timeZone: 'UTC' });
            els.range.textContent = 'Month: ' + month + ' ' + dt.getUTCFullYear();
        }

        async function loadBookingsForRange() {
            computeRange();
            setRangeLabel();
            setActiveViewButtons();

            if (!els.loading) return;
            els.loading.style.display = 'block';

            // Month view still loads bookings so week-switch is instant.
            const start = formatYmd(state.rangeStartMs);
            const end = formatYmd(state.rangeEndMs - 86400000);
            const params = new URLSearchParams({ start, end, tz: sf.timezone });

            if (els.branch && els.branch.value) {
                params.set('branch_id', String(els.branch.value));
            }

            try {
                const res = await fetch(`/app/sharpfleet/admin/bookings/feed?${params.toString()}`, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) {
                    const msg = await getResponseErrorMessage(res);
                    if (window.SharpFleetModal && typeof window.SharpFleetModal.notice === 'function') {
                        window.SharpFleetModal.notice('Bookings', msg || 'Could not load bookings.');
                    }
                    state.bookings = [];
                    render();
                    return;
                }
                const data = await res.json();
                state.bookings = Array.isArray(data.bookings) ? data.bookings : [];
                render();
            } catch (e) {
                if (window.SharpFleetModal && typeof window.SharpFleetModal.notice === 'function') {
                    window.SharpFleetModal.notice('Bookings', 'Could not load bookings (network error).');
                }
                state.bookings = [];
                render();
            } finally {
                els.loading.style.display = 'none';
            }
        }

        function clearCalendar() {
            if (els.cal) {
                els.cal.innerHTML = '';
            }
        }

        function renderMonth() {
            clearCalendar();
            if (!els.cal) return;

            // Utilisation indicators only (no bookings rendered in month view).
            const totalVehicles = Array.isArray(sf.vehicles) ? sf.vehicles.length : 0;
            const vehicleUsageByDay = new Map(); // ymd -> Set(vehicle_id)
            state.bookings.forEach(b => {
                const startMs = parseYmdHi(b.planned_start_local);
                const endMs = parseYmdHi(b.planned_end_local);
                if (!isFinite(startMs) || !isFinite(endMs)) return;

                // For month indicators, count a vehicle as "used" on each day it overlaps.
                const dayStart = parseYmd(formatYmd(startMs));
                const dayEnd = parseYmd(formatYmd(endMs));
                for (let t = dayStart; t <= dayEnd; t += 86400000) {
                    const ymd = formatYmd(t);
                    if (!vehicleUsageByDay.has(ymd)) vehicleUsageByDay.set(ymd, new Set());
                    vehicleUsageByDay.get(ymd).add(String(b.vehicle_id));
                }
            });

            const monthStart = firstDayOfMonth(state.anchorMs);
            const monthEndDay = lastDayOfMonth(state.anchorMs);
            // Monday-based grid
            const firstDow = new Date(monthStart).getUTCDay();
            const shift = (firstDow + 6) % 7;
            const gridStart = monthStart - shift * 86400000;

            const table = document.createElement('table');
            table.className = 'table';
            const thead = document.createElement('thead');
            thead.innerHTML = '<tr><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th></tr>';
            table.appendChild(thead);

            const tbody = document.createElement('tbody');
            for (let w = 0; w < 6; w++) {
                const tr = document.createElement('tr');
                for (let d = 0; d < 7; d++) {
                    const cellMs = gridStart + (w * 7 + d) * 86400000;
                    const cellDate = formatYmd(cellMs);
                    const inMonth = cellMs >= monthStart && cellMs <= monthEndDay;

                    const td = document.createElement('td');
                    td.style.verticalAlign = 'top';

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-secondary btn-sm';
                    btn.style.padding = '4px 8px';
                    btn.style.opacity = inMonth ? '1' : '0.4';
                    btn.textContent = String(new Date(cellMs).getUTCDate());
                    btn.dataset.date = cellDate;
                    btn.addEventListener('click', () => {
                        state.view = 'week';
                        state.anchorMs = parseYmd(btn.dataset.date);
                        loadBookingsForRange();
                    });

                    td.appendChild(btn);

                    const usedSet = vehicleUsageByDay.get(cellDate);
                    const usedCount = usedSet ? usedSet.size : 0;
                    const pct = totalVehicles > 0 ? Math.min(100, Math.round((usedCount / totalVehicles) * 100)) : 0;
                    const ind = document.createElement('div');
                    ind.className = 'sf-bk-month-ind';
                    ind.innerHTML = `<div class="sf-bk-month-bar"><span style="width:${pct}%;"></span></div>` +
                        `<div class="sf-bk-month-txt">${usedCount}/${totalVehicles}</div>`;
                    td.appendChild(ind);

                    tr.appendChild(td);
                }
                tbody.appendChild(tr);
            }

            table.appendChild(tbody);
            els.cal.appendChild(table);
        }

        function renderTimeline() {
            clearCalendar();
            if (!els.cal) return;

            // Locked grid: hour-based columns, teal hour dividers and thicker teal day separators.
            const pxPerHour = (state.view === 'day') ? 92 : 72;
            const pxPerMin = pxPerHour / 60;
            const minutes = Math.round((state.rangeEndMs - state.rangeStartMs) / 60000);
            const timelineWidth = Math.max(720, Math.round(minutes * pxPerMin));

            const scroll = document.createElement('div');
            scroll.className = 'sf-bk-scroll';

            const headerRow = document.createElement('div');
            headerRow.className = 'd-flex sf-bk-header';

            const leftHeader = document.createElement('div');
            leftHeader.className = 'sf-bk-left';
            leftHeader.innerHTML = '<div class="sf-bk-left-inner"><strong>Vehicles</strong></div>';

            const timeHeader = document.createElement('div');
            timeHeader.className = 'sf-bk-time-header';
            timeHeader.style.minWidth = timelineWidth + 'px';

            // Hour labels (left axis labels are not used in this horizontal-time layout; labels live in header).
            for (let t = state.rangeStartMs; t <= state.rangeEndMs; t += 3600000) {
                const minsFromStart = Math.round((t - state.rangeStartMs) / 60000);
                const x = Math.round(minsFromStart * pxPerMin);

                const lbl = document.createElement('div');
                lbl.className = 'sf-bk-time-label';
                lbl.style.left = x + 'px';

                const dt = new Date(t);
                const hm = pad2(dt.getUTCHours()) + ':00';
                // In week view, only show date at midnight ticks to reduce clutter.
                if (state.view === 'week' && dt.getUTCHours() === 0) {
                    const day = pad2(dt.getUTCDate()) + '/' + pad2(dt.getUTCMonth() + 1);
                    lbl.textContent = day;
                    lbl.style.fontWeight = '700';
                } else {
                    lbl.textContent = hm;
                }
                timeHeader.appendChild(lbl);
            }

            headerRow.appendChild(leftHeader);
            headerRow.appendChild(timeHeader);
            scroll.appendChild(headerRow);

            const vehicleRowEls = new Map();

            sf.vehicles.forEach(v => {
                const row = document.createElement('div');
                row.className = 'sf-bk-row';

                const left = document.createElement('div');
                left.className = 'sf-bk-left';
                left.innerHTML = '<div class="sf-bk-left-inner">' +
                    '<div class="fw-bold">' + (v.name || '—') + '</div>' +
                    (v.registration_number ? '<div class="text-muted small">' + v.registration_number + '</div>' : '') +
                    '</div>';

                const lane = document.createElement('div');
                lane.className = 'sf-bk-lane sf-bk-lane-bg';
                lane.style.minWidth = timelineWidth + 'px';
                lane.dataset.vehicleId = String(v.id);

                // Teal hour grid + thicker teal day separators.
                const hourLine = 'rgba(44,191,174,.28)';
                const dayLine = 'rgba(44,191,174,.75)';
                lane.style.backgroundImage =
                    `repeating-linear-gradient(to right, transparent, transparent ${pxPerHour - 1}px, ${hourLine} ${pxPerHour - 1}px, ${hourLine} ${pxPerHour}px),` +
                    `repeating-linear-gradient(to right, transparent, transparent ${pxPerHour * 24 - 2}px, ${dayLine} ${pxPerHour * 24 - 2}px, ${dayLine} ${pxPerHour * 24}px)`;

                const slotHover = document.createElement('div');
                slotHover.className = 'sf-bk-slot-hover';
                lane.appendChild(slotHover);

                lane.addEventListener('mousemove', (ev) => {
                    const rect = lane.getBoundingClientRect();
                    const x = ev.clientX - rect.left;
                    const minutesFromStart = Math.max(0, Math.round(x / pxPerMin));
                    const snapped = Math.floor(minutesFromStart / 60) * 60;
                    const leftPx = Math.round(snapped * pxPerMin);
                    slotHover.style.left = leftPx + 'px';
                    slotHover.style.width = Math.max(12, Math.round(60 * pxPerMin)) + 'px';
                    slotHover.style.display = 'block';
                });
                lane.addEventListener('mouseleave', () => {
                    slotHover.style.display = 'none';
                });

                lane.addEventListener('click', (ev) => {
                    const target = ev.target;
                    if (target && target.closest && target.closest('[data-booking-id]')) {
                        return;
                    }

                    const rect = lane.getBoundingClientRect();
                    const x = ev.clientX - rect.left;
                    const minutesFromStart = Math.max(0, Math.round(x / pxPerMin));
                    const snapped = Math.floor(minutesFromStart / 60) * 60;

                    const startMs = state.rangeStartMs + snapped * 60000;
                    const endMs = startMs + 60 * 60000;

                    openCreateModal({
                        vehicleId: v.id,
                        startMs,
                        endMs,
                    });
                });

                row.appendChild(left);
                row.appendChild(lane);
                scroll.appendChild(row);
                vehicleRowEls.set(String(v.id), lane);
            });

            // Render booking blocks
            state.bookings.forEach(b => {
                const lane = vehicleRowEls.get(String(b.vehicle_id));
                if (!lane) return;

                const startMs = parseYmdHi(b.planned_start_local);
                const endMs = parseYmdHi(b.planned_end_local);
                if (!isFinite(startMs) || !isFinite(endMs)) return;

                const clampedStart = Math.max(startMs, state.rangeStartMs);
                const clampedEnd = Math.min(endMs, state.rangeEndMs);
                if (clampedEnd <= state.rangeStartMs || clampedStart >= state.rangeEndMs) return;

                const leftPx = Math.round(((clampedStart - state.rangeStartMs) / 60000) * pxPerMin);
                const widthPx = Math.max(12, Math.round(((clampedEnd - clampedStart) / 60000) * pxPerMin));

                const block = document.createElement('div');
                block.className = 'sf-bk-block';
                block.style.left = leftPx + 'px';
                block.style.width = widthPx + 'px';
                block.dataset.bookingId = String(b.id);

                const title = (b.driver_name || 'Driver') + (b.customer_name ? ' · ' + b.customer_name : '');
                const time = b.planned_start_local.split(' ')[1] + ' → ' + b.planned_end_local.split(' ')[1];
                block.innerHTML = '<div class="sf-bk-block-title">' + title + '</div>' +
                    '<div class="sf-bk-block-time">' + time + '</div>';

                block.addEventListener('click', (ev) => {
                    ev.stopPropagation();
                    openEditModal(b);
                });

                lane.appendChild(block);
            });

            els.cal.appendChild(scroll);

            // Default visible range: 06:00–18:00 (scroll to 06:00).
            const defaultStartHour = 6;
            const initialScrollLeft = Math.round((defaultStartHour * 60) * pxPerMin);
            scroll.scrollLeft = initialScrollLeft;
        }

        function render() {
            if (state.view === 'month') {
                renderMonth();
                return;
            }
            renderTimeline();
        }

        function resetCreateModalFields() {
            if (els.createForm) els.createForm.reset();
            if (els.createVehicle) {
                els.createVehicle.innerHTML = '<option value="">— Select vehicle —</option>';
                els.createVehicle.disabled = true;
            }
            if (els.createVehicleStatus) {
                els.createVehicleStatus.textContent = 'Select start/end date & time to load available vehicles.';
            }
            if (els.createSubmit) {
                els.createSubmit.disabled = true;
            }
        }

        function setVehicleOptions(selectEl, vehicles) {
            if (!selectEl) return;
            selectEl.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = vehicles.length ? '— Select vehicle —' : 'No vehicles available';
            selectEl.appendChild(placeholder);

            vehicles.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = v.registration_number ? `${v.name} (${v.registration_number})` : v.name;
                selectEl.appendChild(opt);
            });
        }

        async function loadAvailableVehiclesForCreate(preselectVehicleId) {
            if (!els.createVehicle || !els.createStartDate || !els.createStartHour || !els.createStartMinute || !els.createEndDate || !els.createEndHour || !els.createEndMinute) return;

            const branchId = els.createBranch ? (els.createBranch.value || '') : '';
            const startDate = els.createStartDate.value;
            const startHour = els.createStartHour.value;
            const startMinute = els.createStartMinute.value;
            const endDate = els.createEndDate.value;
            const endHour = els.createEndHour.value;
            const endMinute = els.createEndMinute.value;

            if (!startDate || !startHour || !startMinute || !endDate || !endHour || !endMinute) {
                els.createVehicle.disabled = true;
                setVehicleOptions(els.createVehicle, []);
                if (els.createVehicleStatus) {
                    els.createVehicleStatus.textContent = 'Select start/end date & time to load available vehicles.';
                }
                if (els.createSubmit) els.createSubmit.disabled = true;
                return;
            }

            els.createVehicle.disabled = true;
            if (els.createVehicleStatus) {
                els.createVehicleStatus.textContent = 'Loading available vehicles…';
            }

            const params = new URLSearchParams({
                branch_id: branchId,
                planned_start_date: startDate,
                planned_start_hour: startHour,
                planned_start_minute: startMinute,
                planned_end_date: endDate,
                planned_end_hour: endHour,
                planned_end_minute: endMinute,
            });

            try {
                const res = await fetch(`/app/sharpfleet/admin/bookings/available-vehicles?${params.toString()}`, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) {
                    const msg = await getResponseErrorMessage(res);
                    setVehicleOptions(els.createVehicle, []);
                    els.createVehicle.disabled = true;
                    if (els.createVehicleStatus) {
                        els.createVehicleStatus.textContent = msg || 'Could not load vehicles for that time window.';
                    }
                    if (els.createSubmit) els.createSubmit.disabled = true;
                    return;
                }

                const data = await res.json();
                const vehicles = Array.isArray(data.vehicles) ? data.vehicles : [];
                setVehicleOptions(els.createVehicle, vehicles);
                els.createVehicle.disabled = false;

                if (preselectVehicleId) {
                    els.createVehicle.value = String(preselectVehicleId);
                }

                if (els.createVehicleStatus) {
                    els.createVehicleStatus.textContent = vehicles.length
                        ? `Available vehicles: ${vehicles.length}`
                        : 'No vehicles available for this time window.';
                }

                if (els.createSubmit) {
                    els.createSubmit.disabled = !els.createVehicle.value;
                }
            } catch (e) {
                setVehicleOptions(els.createVehicle, []);
                els.createVehicle.disabled = true;
                if (els.createVehicleStatus) {
                    els.createVehicleStatus.textContent = 'Could not load vehicles (network error).';
                }
                if (els.createSubmit) els.createSubmit.disabled = true;
            }
        }

        function openCreateModal({ vehicleId, startMs, endMs }) {
            resetCreateModalFields();

            if (els.createBranch && els.branch) {
                els.createBranch.value = els.branch.value || '';
            }

            const start = new Date(startMs);
            const end = new Date(endMs);

            if (els.createStartDate) els.createStartDate.value = formatYmd(startMs);
            if (els.createStartHour) els.createStartHour.value = pad2(start.getUTCHours());
            if (els.createStartMinute) els.createStartMinute.value = pad2(start.getUTCMinutes());
            if (els.createEndDate) els.createEndDate.value = formatYmd(endMs);
            if (els.createEndHour) els.createEndHour.value = pad2(end.getUTCHours());
            if (els.createEndMinute) els.createEndMinute.value = pad2(end.getUTCMinutes());

            show(els.createModal);
            loadAvailableVehiclesForCreate(vehicleId);
        }

        function closeCreateModal() {
            hide(els.createModal);
        }

        function openEditModal(b) {
            if (!els.editModal || !els.editForm) return;

            els.editId.value = String(b.id);
            els.editForm.action = `/app/sharpfleet/admin/bookings/${b.id}`;

            if (els.editSubtitle) {
                const startMs = parseYmdHi(b.planned_start_local);
                const endMs = parseYmdHi(b.planned_end_local);
                els.editSubtitle.textContent = `${b.vehicle_name || 'Vehicle'} · ${formatDmyHi(startMs)} → ${formatDmyHi(endMs)} (${b.timezone || sf.timezone})`;
            }

            if (els.editCreatedByNotice) {
                const hasCreator = b.created_by_name && String(b.created_by_name).trim() !== '' && b.created_by_user_id;
                if (hasCreator && Number(b.created_by_user_id) !== Number(sf.currentUserId)) {
                    els.editCreatedByNotice.style.display = 'block';
                    els.editCreatedByNotice.textContent = `Created by ${b.created_by_name}. They will be notified about any changes.`;
                } else {
                    els.editCreatedByNotice.style.display = 'none';
                    els.editCreatedByNotice.textContent = '';
                }
            }

            if (els.editBranch && typeof b.branch_id !== 'undefined' && b.branch_id !== null) {
                els.editBranch.value = String(b.branch_id);
            } else if (els.editBranch) {
                els.editBranch.value = '';
            }

            if (els.editDriver) els.editDriver.value = String(b.user_id || '');
            if (els.editVehicle) els.editVehicle.value = String(b.vehicle_id || '');

            const startMs = parseYmdHi(b.planned_start_local);
            const endMs = parseYmdHi(b.planned_end_local);
            const start = new Date(startMs);
            const end = new Date(endMs);

            if (els.editStartDate) els.editStartDate.value = formatYmd(startMs);
            if (els.editStartHour) els.editStartHour.value = pad2(start.getUTCHours());
            if (els.editStartMinute) els.editStartMinute.value = pad2(start.getUTCMinutes());

            if (els.editEndDate) els.editEndDate.value = formatYmd(endMs);
            if (els.editEndHour) els.editEndHour.value = pad2(end.getUTCHours());
            if (els.editEndMinute) els.editEndMinute.value = pad2(end.getUTCMinutes());

            if (els.editRemindMe) {
                els.editRemindMe.checked = Number(b.remind_me || 0) === 1;
            }

            if (els.editCustomer) {
                els.editCustomer.value = b.customer_id ? String(b.customer_id) : '';
            }
            if (els.editCustomerName) {
                els.editCustomerName.value = String(b.customer_name || '');
            }
            if (els.editNotes) {
                els.editNotes.value = String(b.notes || '');
            }

            show(els.editModal);
        }

        function closeEditModal() {
            hide(els.editModal);
        }

        function wireCustomerFields(selectEl, inputEl) {
            if (!selectEl || !inputEl) return;
            selectEl.addEventListener('change', () => {
                if (selectEl.value) {
                    inputEl.value = '';
                }
            });
            inputEl.addEventListener('input', () => {
                if (String(inputEl.value || '').trim()) {
                    selectEl.value = '';
                }
            });
        }

        // View controls
        if (els.viewDay) els.viewDay.addEventListener('click', () => { state.view = 'day'; loadBookingsForRange(); });
        if (els.viewWeek) els.viewWeek.addEventListener('click', () => { state.view = 'week'; loadBookingsForRange(); });
        if (els.viewMonth) els.viewMonth.addEventListener('click', () => { state.view = 'month'; loadBookingsForRange(); });

        if (els.today) els.today.addEventListener('click', () => { state.anchorMs = parseYmd(sf.today); loadBookingsForRange(); });
        if (els.prev) els.prev.addEventListener('click', () => {
            if (state.view === 'day') state.anchorMs -= 86400000;
            else if (state.view === 'week') state.anchorMs -= 7 * 86400000;
            else {
                const dt = new Date(state.anchorMs);
                state.anchorMs = Date.UTC(dt.getUTCFullYear(), dt.getUTCMonth() - 1, 1, 0, 0, 0, 0);
            }
            loadBookingsForRange();
        });
        if (els.next) els.next.addEventListener('click', () => {
            if (state.view === 'day') state.anchorMs += 86400000;
            else if (state.view === 'week') state.anchorMs += 7 * 86400000;
            else {
                const dt = new Date(state.anchorMs);
                state.anchorMs = Date.UTC(dt.getUTCFullYear(), dt.getUTCMonth() + 1, 1, 0, 0, 0, 0);
            }
            loadBookingsForRange();
        });

        // Create modal wiring
        if (els.createClose) els.createClose.addEventListener('click', closeCreateModal);
        if (els.createCancelBtn) els.createCancelBtn.addEventListener('click', closeCreateModal);
        if (els.createVehicle) {
            els.createVehicle.addEventListener('change', () => {
                if (els.createSubmit) els.createSubmit.disabled = !els.createVehicle.value;
            });
        }
        [els.createStartDate, els.createStartHour, els.createStartMinute, els.createEndDate, els.createEndHour, els.createEndMinute, els.createBranch].forEach(el => {
            if (!el) return;
            el.addEventListener('change', () => loadAvailableVehiclesForCreate());
        });
        wireCustomerFields(els.createCustomer, els.createCustomerName);

        // Edit modal wiring
        if (els.editClose) els.editClose.addEventListener('click', closeEditModal);
        if (els.editCloseBtn) els.editCloseBtn.addEventListener('click', closeEditModal);
        wireCustomerFields(els.editCustomer, els.editCustomerName);

        if (els.editCancelBooking) {
            els.editCancelBooking.addEventListener('click', () => {
                const bookingId = els.editId ? els.editId.value : '';
                if (!bookingId) return;

                const doCancel = () => {
                    if (!els.cancelForm) return;
                    els.cancelForm.action = `/app/sharpfleet/admin/bookings/${bookingId}/cancel`;
                    els.cancelForm.submit();
                };

                if (window.SharpFleetModal && typeof window.SharpFleetModal.confirm === 'function') {
                    window.SharpFleetModal.confirm({
                        title: 'Cancel booking',
                        message: 'Cancel this booking? The driver will be emailed.',
                        confirmText: 'Cancel booking',
                        cancelText: 'Keep booking',
                        confirmVariant: 'danger',
                        onConfirm: doCancel,
                    });
                } else {
                    doCancel();
                }
            });
        }

        // Initial load
        loadBookingsForRange();
    })();
</script>
@endif

@endsection
