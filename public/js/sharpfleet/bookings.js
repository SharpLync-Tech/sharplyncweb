// Booking block tooltip logic
document.addEventListener('DOMContentLoaded', function() {
    const tooltip = document.createElement('div');
    tooltip.style.position = 'fixed';
    tooltip.style.background = '#0A2A4D';
    tooltip.style.color = '#fff';
    tooltip.style.padding = '10px 16px';
    tooltip.style.borderRadius = '8px';
    tooltip.style.fontSize = '15px';
    tooltip.style.boxShadow = '0 4px 16px rgba(10,42,77,0.18)';
    tooltip.style.zIndex = '9999';
    tooltip.style.pointerEvents = 'none';
    tooltip.style.maxWidth = '340px';
    tooltip.style.whiteSpace = 'pre-line';
    tooltip.style.display = 'none';
    document.body.appendChild(tooltip);

    function showTooltip(e, text) {
        tooltip.textContent = text;
        tooltip.style.display = 'block';
        const offset = 18;
        let x = e.clientX + offset;
        let y = e.clientY + offset;
        // Prevent overflow
        if (x + tooltip.offsetWidth > window.innerWidth) {
            x = window.innerWidth - tooltip.offsetWidth - 12;
        }
        if (y + tooltip.offsetHeight > window.innerHeight) {
            y = window.innerHeight - tooltip.offsetHeight - 12;
        }
        tooltip.style.left = x + 'px';
        tooltip.style.top = y + 'px';
    }

    function hideTooltip() {
        tooltip.style.display = 'none';
    }

    document.querySelectorAll('.sf-bk-block[data-tooltip]').forEach(function(block) {
        block.addEventListener('mouseenter', function(e) {
            showTooltip(e, block.getAttribute('data-tooltip'));
        });
        block.addEventListener('mousemove', function(e) {
            showTooltip(e, block.getAttribute('data-tooltip'));
        });
        block.addEventListener('mouseleave', hideTooltip);
        block.addEventListener('touchstart', function(e) {
            showTooltip(e.touches[0], block.getAttribute('data-tooltip'));
        });
        block.addEventListener('touchend', hideTooltip);
    });
});
(function () {
    const sf = window.SharpFleetBookingsConfig || null;
    if (!sf || !sf.timezone || !sf.today) return;

    sf.currentUserId = Number(sf.currentUserId || 0);
    sf.canEditBookings = !!sf.canEditBookings;
    sf.vehicles = Array.isArray(sf.vehicles) ? sf.vehicles : [];
    sf.drivers = Array.isArray(sf.drivers) ? sf.drivers : [];
    sf.branchesEnabled = !!sf.branchesEnabled;
    sf.branches = Array.isArray(sf.branches) ? sf.branches : [];
    sf.customersEnabled = !!sf.customersEnabled;

    const els = {
        offlineNotice: document.getElementById('sfOfflineNotice'),
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
        createVehicleSection: document.getElementById('sfBkCreateVehicleSection'),
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
        createNotes: document.getElementById('sfBkCreateNotes'),

        editModal: document.getElementById('sfBkEditModal'),
        editClose: document.getElementById('sfBkEditClose'),
        editCloseBtn: document.getElementById('sfBkEditCloseBtn'),
        editTitle: document.getElementById('sfBkEditTitle'),
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
        editActions: document.getElementById('sfBkEditActions'),
        editSubmit: document.getElementById('sfBkEditSubmit'),
        cancelForm: document.getElementById('sfBkCancelForm'),
    };

    function show(el) { if (el) el.style.display = 'block'; }
    function hide(el) { if (el) el.style.display = 'none'; }

    function updateOfflineNotice() {
        if (!els.offlineNotice) return;
        if (navigator.onLine === false) {
            show(els.offlineNotice);
        } else {
            hide(els.offlineNotice);
        }
    }

    function pad2(n) { return String(n).padStart(2, '0'); }

    function selectedBranchId() {
        if (!els.branch) return null;
        const v = String(els.branch.value || '').trim();
        return v ? v : null;
    }

    function visibleVehicles() {
        const branchId = selectedBranchId();
        const all = Array.isArray(sf.vehicles) ? sf.vehicles : [];
        if (!branchId) return all;
        return all.filter(v => String(v.branch_id || '') === branchId);
    }

    function localeFromTimezone(tz) {
        const s = String(tz || '').toLowerCase();
        if (s.includes('australia')) return 'en-AU';
        if (s.includes('america') || s.startsWith('us/')) return 'en-US';
        return 'en-GB';
    }

    const sfLocale = localeFromTimezone(sf.timezone);

    function formatDisplayDate(ms) {
        try {
            return new Intl.DateTimeFormat(sfLocale, {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                timeZone: 'UTC',
            }).format(new Date(ms));
        } catch (e) {
            return formatYmd(ms);
        }
    }

    function parseYmd(ymd) {
        const parts = String(ymd).split('-').map(Number);
        const y = parts[0];
        const m = parts[1];
        const d = parts[2];
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
        return formatDisplayDate(ms) + ' ' + pad2(dt.getUTCHours()) + ':' + pad2(dt.getUTCMinutes());
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
        view: 'day',
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
            els.range.textContent = 'Day: ' + formatDisplayDate(state.rangeStartMs);
            return;
        }
        if (state.view === 'week') {
            const endMs = state.rangeEndMs - 60000;
            els.range.textContent = 'Week: ' + formatDisplayDate(state.rangeStartMs) + ' → ' + formatDisplayDate(endMs);
            return;
        }
        const dt = new Date(state.anchorMs);
        const month = dt.toLocaleString(sfLocale, { month: 'long', timeZone: 'UTC' });
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
                // Day view must fail silently.
                if (state.view !== 'day' && window.SharpFleetModal && typeof window.SharpFleetModal.notice === 'function') {
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
            // Day view must fail silently.
            if (state.view !== 'day' && window.SharpFleetModal && typeof window.SharpFleetModal.notice === 'function') {
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

    function enableFakeStickyLeft(scrollEl) {
        if (!scrollEl) return;

        scrollEl.classList.add('sf-bk-fake-sticky');
        const stickyEls = Array.from(scrollEl.querySelectorAll('.sf-bk-left'));

        const apply = () => {
            const x = scrollEl.scrollLeft || 0;
            for (const el of stickyEls) {
                el.style.transform = `translateX(${x}px)`;
            }
        };

        scrollEl.addEventListener('scroll', apply, { passive: true });
        apply();
    }

    function renderMonth() {
        clearCalendar();
        if (!els.cal) return;

        // Utilisation indicators only (no bookings rendered in month view).
        const vehicles = visibleVehicles();
        const totalVehicles = vehicles.length;
        const vehicleNameById = new Map();
        vehicles.forEach(v => vehicleNameById.set(String(v.id), String(v.name || v.registration_number || 'Vehicle')));
        const vehicleUsageByDay = new Map(); // ymd -> Set(vehicle_id)
        state.bookings.forEach(b => {
            const startMs = parseYmdHi(b.planned_start_local);
            const endMs = parseYmdHi(b.planned_end_local);
            if (!isFinite(startMs) || !isFinite(endMs)) return;

            // Treat end time as exclusive for day-overlap counting.
            // Example: 23:00 → 00:00 should count only on the start day, not the next day.
            const endExclusiveMs = endMs - 1;
            if (endExclusiveMs < startMs) return;

            // For month indicators, count a vehicle as "used" on each day it overlaps.
            const dayStart = parseYmd(formatYmd(startMs));
            const dayEnd = parseYmd(formatYmd(endExclusiveMs));
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
                td.className = 'sf-bk-month-cell';
                td.style.verticalAlign = 'top';

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-secondary btn-sm';
                btn.style.display = 'block';
                btn.style.width = '100%';
                btn.style.padding = '10px';
                btn.style.textAlign = 'left';
                btn.style.opacity = inMonth ? '1' : '0.4';
                btn.textContent = String(new Date(cellMs).getUTCDate());
                btn.dataset.date = cellDate;
                btn.addEventListener('click', () => {
                    const dayStartMs = parseYmd(btn.dataset.date);
                    const startMs = dayStartMs + 9 * 3600000;
                    const endMs = startMs + 60 * 60000;
                    openCreateModal({
                        vehicleId: null,
                        startMs,
                        endMs,
                    });
                    if (els.createStartHour && typeof els.createStartHour.focus === 'function') {
                        setTimeout(() => els.createStartHour.focus(), 0);
                    }
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

                if (usedCount > 0 && usedSet) {
                    const tip = document.createElement('div');
                    tip.className = 'sf-bk-month-tip';

                    const title = document.createElement('div');
                    title.className = 'sf-bk-month-tip-title';
                    title.textContent = 'Booked vehicles';

                    const list = document.createElement('div');
                    list.className = 'sf-bk-month-tip-list';

                    const names = Array.from(usedSet).map(id => vehicleNameById.get(String(id)) || 'Vehicle');
                    names.sort((a, b) => String(a).localeCompare(String(b)));
                    const shown = names.slice(0, 6);
                    const extra = names.length - shown.length;
                    list.textContent = shown.join(', ') + (extra > 0 ? ` + ${extra} more` : '');

                    tip.appendChild(title);
                    tip.appendChild(list);
                    td.appendChild(tip);

                    // Show tooltip when hovering the day cell (users naturally hover the day button).
                    const showTip = () => { tip.style.display = 'block'; };
                    const hideTip = () => { tip.style.display = 'none'; };
                    td.addEventListener('mouseenter', showTip);
                    td.addEventListener('mouseleave', hideTip);
                    btn.addEventListener('mouseenter', showTip);
                    btn.addEventListener('mouseleave', hideTip);
                }

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
                const day = new Intl.DateTimeFormat(sfLocale, {
                    day: '2-digit',
                    month: '2-digit',
                    timeZone: 'UTC',
                }).format(new Date(t));
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

        visibleVehicles().forEach(v => {
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
            lane.style.cursor = 'pointer';

            // Teal hour grid + thicker teal day separators.
            const hourLine = 'rgba(44,191,174,.28)';
            const dayLine = 'rgba(44,191,174,.75)';
            lane.style.backgroundImage =
                `repeating-linear-gradient(to right, transparent, transparent ${pxPerHour - 1}px, ${hourLine} ${pxPerHour - 1}px, ${hourLine} ${pxPerHour}px),` +
                `repeating-linear-gradient(to right, transparent, transparent ${pxPerHour * 24 - 2}px, ${dayLine} ${pxPerHour * 24 - 2}px, ${dayLine} ${pxPerHour * 24}px)`;

            const slotHover = document.createElement('div');
            slotHover.className = 'sf-bk-slot-hover';
            slotHover.innerHTML = '<div class="sf-bk-slot-tip">Book this time</div><div class="sf-bk-slot-plus">+</div>';
            lane.appendChild(slotHover);

            lane.addEventListener('mousemove', (ev) => {
                // Don't show the "Book this time" hover when pointing at an existing booking.
                const target = ev.target;
                if (target && target.closest && target.closest('[data-booking-id]')) {
                    slotHover.style.display = 'none';
                    return;
                }
                const rect = lane.getBoundingClientRect();
                const x = ev.clientX - rect.left;
                const minutesFromStart = Math.max(0, Math.round(x / pxPerMin));
                const snapped = Math.floor(minutesFromStart / 60) * 60;
                const leftPx = Math.round(snapped * pxPerMin);
                slotHover.style.left = leftPx + 'px';
                slotHover.style.width = Math.max(12, Math.round(60 * pxPerMin)) + 'px';
                slotHover.style.display = 'flex';
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
        let earliestBookingStartMs = null;
        state.bookings.forEach(b => {
            const lane = vehicleRowEls.get(String(b.vehicle_id));
            if (!lane) return;

            const startMs = parseYmdHi(b.planned_start_local);
            const endMs = parseYmdHi(b.planned_end_local);
            if (!isFinite(startMs) || !isFinite(endMs)) return;

            if (startMs >= state.rangeStartMs && startMs < state.rangeEndMs) {
                if (earliestBookingStartMs === null || startMs < earliestBookingStartMs) {
                    earliestBookingStartMs = startMs;
                }
            }

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

        // Default visible range: scroll to the earliest booking (so overnight bookings aren't "missing").
        // Falls back to 06:00 if there are no bookings.
        const fallbackStartHour = 6;
        if (earliestBookingStartMs !== null) {
            const targetMs = Math.max(state.rangeStartMs, earliestBookingStartMs - 60 * 60000);
            scroll.scrollLeft = Math.max(0, Math.round(((targetMs - state.rangeStartMs) / 60000) * pxPerMin));
        } else {
            scroll.scrollLeft = Math.round((fallbackStartHour * 60) * pxPerMin);
        }

        // Keep the left vehicle column pinned during horizontal scroll, even if CSS sticky is broken.
        enableFakeStickyLeft(scroll);
    }

    function renderWeekGrid() {
        // Week-specific rendering: 7 day columns (Mon–Sun) with vertical time axis (06:00–18:00).
        // This intentionally diverges from Day view (which is a horizontal timeline).
        clearCalendar();
        if (!els.cal) return;

        const startHour = 6;
        const endHour = 18;
        const pxPerHour = 56;
        const pxPerMin = pxPerHour / 60;
        const dayColWidth = 180;
        const dayMs = 86400000;

        const scroll = document.createElement('div');
        scroll.className = 'sf-bk-scroll';

        const headerRow = document.createElement('div');
        headerRow.className = 'd-flex sf-bk-header';

        const leftHeader = document.createElement('div');
        leftHeader.className = 'sf-bk-left';
        leftHeader.innerHTML = '<div class="sf-bk-left-inner"><strong>Vehicles</strong></div>';

        const daysHeader = document.createElement('div');
        daysHeader.className = 'sf-wk-days';
        daysHeader.style.minWidth = (7 * dayColWidth) + 'px';

        const dayNames = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
        for (let i = 0; i < 7; i++) {
            const dayStartMs = state.rangeStartMs + i * dayMs;
            const dt = new Date(dayStartMs);
            const dowMon0 = (dt.getUTCDay() + 6) % 7; // 0=Mon
            const dayName = dayNames[dowMon0];
            const dayDate = new Intl.DateTimeFormat(sfLocale, {
                day: '2-digit',
                month: '2-digit',
                timeZone: 'UTC',
            }).format(new Date(dayStartMs));
            const ymd = formatYmd(dayStartMs);

            const head = document.createElement('div');
            head.className = 'sf-wk-dayhead' + (ymd === sf.today ? ' is-today' : '');
            head.innerHTML = '<div class="sf-wk-dayname">' + dayName + '</div>' +
                '<div class="sf-wk-daydate">' + dayDate + '</div>';
            daysHeader.appendChild(head);
        }

        headerRow.appendChild(leftHeader);
        headerRow.appendChild(daysHeader);
        scroll.appendChild(headerRow);

        const dayCellByVehicle = new Map(); // `${vehicleId}:${dayIndex}` -> element

        visibleVehicles().forEach(v => {
            const row = document.createElement('div');
            row.className = 'sf-bk-row';

            const left = document.createElement('div');
            left.className = 'sf-bk-left';
            left.innerHTML = '<div class="sf-bk-left-inner">' +
                '<div class="fw-bold">' + (v.name || '—') + '</div>' +
                (v.registration_number ? '<div class="text-muted small">' + v.registration_number + '</div>' : '') +
                '</div>';

            const days = document.createElement('div');
            days.className = 'sf-wk-days';
            days.style.minWidth = (7 * dayColWidth) + 'px';

            for (let i = 0; i < 7; i++) {
                const dayStartMs = state.rangeStartMs + i * dayMs;
                const ymd = formatYmd(dayStartMs);

                const cell = document.createElement('div');
                cell.className = 'sf-wk-daycell' + (ymd === sf.today ? ' is-today' : '');
                cell.dataset.vehicleId = String(v.id);
                cell.dataset.dayIndex = String(i);

                const lineTop = document.createElement('div');
                lineTop.className = 'sf-wk-strongline top';
                const lineMid = document.createElement('div');
                lineMid.className = 'sf-wk-strongline mid';
                const lineBottom = document.createElement('div');
                lineBottom.className = 'sf-wk-strongline bottom';
                cell.appendChild(lineTop);
                cell.appendChild(lineMid);
                cell.appendChild(lineBottom);

                cell.addEventListener('click', (ev) => {
                    const target = ev.target;
                    if (target && target.closest && target.closest('[data-booking-id]')) {
                        return;
                    }

                    const rect = cell.getBoundingClientRect();
                    const y = ev.clientY - rect.top;
                    const minutesFromStart = Math.max(0, Math.min((endHour - startHour) * 60, Math.round(y / pxPerMin)));
                    const snapped = Math.floor(minutesFromStart / 60) * 60;

                    const windowStart = dayStartMs + startHour * 3600000;
                    let startMs = windowStart + snapped * 60000;
                    let endMs = startMs + 60 * 60000;
                    const windowEnd = dayStartMs + endHour * 3600000;
                    if (endMs > windowEnd) endMs = windowEnd;
                    if (endMs <= startMs) endMs = startMs + 60 * 60000;

                    openCreateModal({
                        vehicleId: v.id,
                        startMs,
                        endMs,
                    });
                });

                days.appendChild(cell);
                dayCellByVehicle.set(String(v.id) + ':' + String(i), cell);
            }

            row.appendChild(left);
            row.appendChild(days);
            scroll.appendChild(row);
        });

        // Render booking blocks inside day columns (clipped to 06:00–18:00).
        state.bookings.forEach(b => {
            const vehicleId = String(b.vehicle_id);
            const startMs = parseYmdHi(b.planned_start_local);
            const endMs = parseYmdHi(b.planned_end_local);
            if (!isFinite(startMs) || !isFinite(endMs)) return;

            for (let i = 0; i < 7; i++) {
                const cell = dayCellByVehicle.get(vehicleId + ':' + String(i));
                if (!cell) continue;

                const dayStartMs = state.rangeStartMs + i * dayMs;
                const windowStart = dayStartMs + startHour * 3600000;
                const windowEnd = dayStartMs + endHour * 3600000;

                const segStart = Math.max(startMs, windowStart);
                const segEnd = Math.min(endMs, windowEnd);
                if (segEnd <= segStart) continue;

                const topPx = Math.round(((segStart - windowStart) / 60000) * pxPerMin);
                const heightPx = Math.max(12, Math.round(((segEnd - segStart) / 60000) * pxPerMin));

                const segStartDt = new Date(segStart);
                const segEndDt = new Date(segEnd);
                const segTime = pad2(segStartDt.getUTCHours()) + ':' + pad2(segStartDt.getUTCMinutes()) + ' → ' +
                    pad2(segEndDt.getUTCHours()) + ':' + pad2(segEndDt.getUTCMinutes());

                const block = document.createElement('div');
                block.className = 'sf-bk-block';
                block.style.top = topPx + 'px';
                block.style.height = heightPx + 'px';
                block.dataset.bookingId = String(b.id);

                const title = (b.driver_name || 'Driver') + (b.customer_name ? ' · ' + b.customer_name : '');
                block.innerHTML = '<div class="sf-bk-block-title">' + title + '</div>' +
                    '<div class="sf-bk-block-time">' + segTime + '</div>';

                block.addEventListener('click', (ev) => {
                    ev.stopPropagation();
                    openEditModal(b);
                });

                cell.appendChild(block);
            }
        });

        els.cal.appendChild(scroll);
    }

    function renderWeekV1() {
        // Week V1: compact grid (Mon–Sun), auto-expand only vehicles with bookings.
        clearCalendar();
        if (!els.cal) return;

        const dayMs = 86400000;
        const weekStartMs = state.rangeStartMs;
        const startHour = 6;
        const endHour = 18;
        const pxPerHour = 56;
        const pxPerMin = pxPerHour / 60;
        const timelineHeight = (endHour - startHour) * pxPerHour;

        const dayNames = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];

        // Bookings by vehicle (only vehicles with at least one booking become expanded).
        const bookingsByVehicle = new Map();
        (state.bookings || []).forEach(b => {
            const vehicleId = String(b.vehicle_id);
            const startMs = parseYmdHi(b.planned_start_local);
            const endMs = parseYmdHi(b.planned_end_local);
            if (!isFinite(startMs) || !isFinite(endMs)) return;
            if (endMs <= weekStartMs || startMs >= state.rangeEndMs) return;
            if (!bookingsByVehicle.has(vehicleId)) bookingsByVehicle.set(vehicleId, []);
            bookingsByVehicle.get(vehicleId).push(b);
        });

        const wrap = document.createElement('div');
        wrap.className = 'sf-wk-v1-scroll';

        const header = document.createElement('div');
        header.className = 'sf-wk-v1-header';

        const leftHead = document.createElement('div');
        leftHead.className = 'sf-wk-v1-left';
        leftHead.innerHTML = '<div class="sf-bk-left-inner"><strong>Vehicles</strong></div>';
        header.appendChild(leftHead);

        for (let i = 0; i < 7; i++) {
            const dayStartMs = weekStartMs + i * dayMs;
            const dt = new Date(dayStartMs);
            const dowMon0 = (dt.getUTCDay() + 6) % 7; // 0=Mon
            const dayName = dayNames[dowMon0];
            const dayDate = new Intl.DateTimeFormat(sfLocale, {
                day: '2-digit',
                month: '2-digit',
                timeZone: 'UTC',
            }).format(new Date(dayStartMs));
            const ymd = formatYmd(dayStartMs);

            const head = document.createElement('div');
            head.className = 'sf-wk-v1-dayhead' + (ymd === sf.today ? ' is-today' : '');
            head.innerHTML = '<div class="sf-wk-dayname">' + dayName + '</div>' +
                '<div class="sf-wk-daydate">' + dayDate + '</div>';
            header.appendChild(head);
        }

        wrap.appendChild(header);

        visibleVehicles().forEach(v => {
            const vehicleId = String(v.id);
            const bookings = bookingsByVehicle.get(vehicleId) || [];
            const isExpanded = bookings.length > 0;

            const row = document.createElement('div');
            row.className = 'sf-wk-v1-row' + (isExpanded ? ' is-expanded' : '');

            const left = document.createElement('div');
            left.className = 'sf-wk-v1-left';
            left.innerHTML = '<div class="sf-bk-left-inner">' +
                '<div class="fw-bold">' + (v.name || '—') + '</div>' +
                (v.registration_number ? '<div class="text-muted small">' + v.registration_number + '</div>' : '') +
                '</div>';
            row.appendChild(left);

            for (let i = 0; i < 7; i++) {
                const dayStartMs = weekStartMs + i * dayMs;
                const ymd = formatYmd(dayStartMs);

                const cell = document.createElement('div');
                cell.className = 'sf-wk-v1-cell' + (ymd === sf.today ? ' is-today' : '');
                cell.dataset.vehicleId = vehicleId;
                cell.dataset.dayIndex = String(i);

                if (!isExpanded) {
                    // Collapsed rows: no hour grid, no time-based positioning.
                    cell.addEventListener('click', (ev) => {
                        const target = ev.target;
                        if (target && target.closest && target.closest('[data-booking-id]')) return;
                        const startMs = dayStartMs + 9 * 3600000;
                        const endMs = startMs + 60 * 60000;
                        openCreateModal({ vehicleId: v.id, startMs, endMs });
                    });

                    row.appendChild(cell);
                    continue;
                }

                // Expanded rows: optional chips (overview) + hour grid + time-positioned blocks.
                const chips = document.createElement('div');
                chips.className = 'sf-wk-v1-chips';
                cell.appendChild(chips);

                const grid = document.createElement('div');
                grid.className = 'sf-wk-v1-grid';
                grid.style.height = timelineHeight + 'px';
                grid.addEventListener('click', (ev) => {
                    const target = ev.target;
                    if (target && target.closest && target.closest('[data-booking-id]')) return;
                    const rect = grid.getBoundingClientRect();
                    const y = ev.clientY - rect.top;
                    const minutesFromStart = Math.max(0, Math.min((endHour - startHour) * 60, Math.round(y / pxPerMin)));
                    const snapped = Math.floor(minutesFromStart / 60) * 60;
                    const windowStart = dayStartMs + startHour * 3600000;
                    let startMs = windowStart + snapped * 60000;
                    let endMs = startMs + 60 * 60000;
                    const windowEnd = dayStartMs + endHour * 3600000;
                    if (endMs > windowEnd) endMs = windowEnd;
                    if (endMs <= startMs) endMs = startMs + 60 * 60000;
                    openCreateModal({ vehicleId: v.id, startMs, endMs });
                });
                cell.appendChild(grid);

                row.appendChild(cell);
            }

            wrap.appendChild(row);
        });

        // Render chips + booking blocks only for expanded rows.
        // Chips: overview (no time positioning). Blocks: accurate time positioning (06:00–18:00).
        const rowCells = wrap.querySelectorAll('.sf-wk-v1-cell');
        const cellByKey = new Map();
        rowCells.forEach(cell => {
            const vehicleId = String(cell.dataset.vehicleId || '');
            const dayIndex = String(cell.dataset.dayIndex || '');
            if (!vehicleId || dayIndex === '') return;
            cellByKey.set(vehicleId + ':' + dayIndex, cell);
        });

        (state.bookings || []).forEach(b => {
            const vehicleId = String(b.vehicle_id);
            if (!bookingsByVehicle.has(vehicleId)) return; // collapsed rows skip all work

            const startMs = parseYmdHi(b.planned_start_local);
            const endMs = parseYmdHi(b.planned_end_local);
            if (!isFinite(startMs) || !isFinite(endMs)) return;

            for (let i = 0; i < 7; i++) {
                const cell = cellByKey.get(vehicleId + ':' + String(i));
                if (!cell) continue;

                const dayStartMs = weekStartMs + i * dayMs;
                const dayEndMs = dayStartMs + dayMs;
                if (endMs <= dayStartMs || startMs >= dayEndMs) continue;

                const chipWrap = cell.querySelector('.sf-wk-v1-chips');
                const grid = cell.querySelector('.sf-wk-v1-grid');
                if (!chipWrap || !grid) continue;

                // Chip time range clipped to the calendar day (overview only).
                const chipSegStart = Math.max(startMs, dayStartMs);
                const chipSegEnd = Math.min(endMs, dayEndMs);
                const chipStartDt = new Date(chipSegStart);
                const chipEndDt = new Date(chipSegEnd);
                const chipTime = pad2(chipStartDt.getUTCHours()) + ':' + pad2(chipStartDt.getUTCMinutes()) + '–' +
                    pad2(chipEndDt.getUTCHours()) + ':' + pad2(chipEndDt.getUTCMinutes());

                const chip = document.createElement('div');
                chip.className = 'sf-wk-v1-chip';
                chip.dataset.bookingId = String(b.id);
                chip.textContent = (b.driver_name || 'Driver') + ' ' + chipTime;
                chip.addEventListener('click', (ev) => {
                    ev.stopPropagation();
                    openEditModal(b);
                });
                chipWrap.appendChild(chip);

                // Time-positioned block clipped to 06:00–18:00 window.
                const windowStart = dayStartMs + startHour * 3600000;
                const windowEnd = dayStartMs + endHour * 3600000;
                const segStart = Math.max(startMs, windowStart);
                const segEnd = Math.min(endMs, windowEnd);
                if (segEnd <= segStart) continue;

                const topPx = Math.round(((segStart - windowStart) / 60000) * pxPerMin);
                const heightPx = Math.max(12, Math.round(((segEnd - segStart) / 60000) * pxPerMin));

                const segStartDt = new Date(segStart);
                const segEndDt = new Date(segEnd);
                const segTime = pad2(segStartDt.getUTCHours()) + ':' + pad2(segStartDt.getUTCMinutes()) + ' → ' +
                    pad2(segEndDt.getUTCHours()) + ':' + pad2(segEndDt.getUTCMinutes());

                const block = document.createElement('div');
                block.className = 'sf-bk-block';
                block.style.top = topPx + 'px';
                block.style.height = heightPx + 'px';
                block.dataset.bookingId = String(b.id);
                const title = (b.driver_name || 'Driver') + (b.customer_name ? ' · ' + b.customer_name : '');
                block.innerHTML = '<div class="sf-bk-block-title">' + title + '</div>' +
                    '<div class="sf-bk-block-time">' + segTime + '</div>';
                block.addEventListener('click', (ev) => {
                    ev.stopPropagation();
                    openEditModal(b);
                });
                grid.appendChild(block);
            }
        });

        els.cal.appendChild(wrap);
    }

    function render() {
        if (state.view === 'month') {
            renderMonth();
            return;
        }

        // Week view diverges from Day view: it is a 7-column grid with vertical time.
        if (state.view === 'week') {
            renderWeekV1();
            return;
        }

        renderTimeline();
    }

    function resetCreateModalFields() {
        if (els.createForm) els.createForm.reset();
        if (els.createVehicleSection) {
            els.createVehicleSection.style.display = 'none';
        }
        if (els.createVehicle) {
            els.createVehicle.innerHTML = '<option value="">— Select vehicle —</option>';
            els.createVehicle.disabled = true;
        }
        if (els.createVehicleStatus) {
            els.createVehicleStatus.textContent = 'Select a future time to see available vehicles.';
        }
        if (els.createSubmit) {
            els.createSubmit.disabled = true;
        }
    }

    function createHasValidWindow() {
        if (!els.createStartDate || !els.createStartHour || !els.createStartMinute || !els.createEndDate || !els.createEndHour || !els.createEndMinute) {
            return false;
        }
        const sd = els.createStartDate.value;
        const sh = els.createStartHour.value;
        const sm = els.createStartMinute.value;
        const ed = els.createEndDate.value;
        const eh = els.createEndHour.value;
        const em = els.createEndMinute.value;

        if (!sd || !sh || !sm || !ed || !eh || !em) return false;

        const start = parseYmdHi(`${sd} ${sh}:${sm}`);
        const end = parseYmdHi(`${ed} ${eh}:${em}`);
        return isFinite(start) && isFinite(end) && end > start;
    }

    function updateCreateVehicleSectionVisibility(preselectVehicleId) {
        if (!els.createVehicleSection) return;

        if (!createHasValidWindow()) {
            els.createVehicleSection.style.display = 'none';
            if (els.createVehicle) {
                els.createVehicle.disabled = true;
                setVehicleOptions(els.createVehicle, []);
            }
            if (els.createVehicleStatus) {
                els.createVehicleStatus.textContent = 'Select a future time to see available vehicles.';
            }
            if (els.createSubmit) els.createSubmit.disabled = true;
            return;
        }

        els.createVehicleSection.style.display = 'block';
        loadAvailableVehiclesForCreate(preselectVehicleId);
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
                els.createVehicleStatus.textContent = 'Select a future time to see available vehicles.';
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

        // If driver dropdown exists, default to current user where applicable.
        if (els.createDriver && els.createDriver.tagName === 'SELECT') {
            const hasOption = Array.from(els.createDriver.options || []).some(o => String(o.value) === String(sf.currentUserId));
            if (hasOption) {
                els.createDriver.value = String(sf.currentUserId);
            }
        }

        show(els.createModal);
        updateCreateVehicleSectionVisibility(vehicleId);
    }

    function closeCreateModal() {
        hide(els.createModal);
    }

    function openEditModal(b) {
        if (!els.editModal || !els.editForm) return;

        const canEdit = !!sf.canEditBookings;
        if (els.editTitle) {
            els.editTitle.textContent = canEdit ? 'Edit booking' : 'Booking details';
        }
        if (els.editCancelBooking) {
            els.editCancelBooking.style.display = canEdit ? '' : 'none';
        }
        if (els.editSubmit) {
            els.editSubmit.style.display = canEdit ? '' : 'none';
        }

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

        // Week-specific divergence note: modal becomes read-only for non-admin viewers.
        if (!canEdit) {
            ['editDriver', 'editVehicle', 'editBranch', 'editStartDate', 'editStartHour', 'editStartMinute', 'editEndDate', 'editEndHour', 'editEndMinute', 'editRemindMe', 'editCustomer', 'editCustomerName', 'editNotes']
                .forEach((key) => {
                    const el = els[key];
                    if (!el) return;
                    el.disabled = true;
                    if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                        el.readOnly = true;
                    }
                });
        } else {
            ['editDriver', 'editVehicle', 'editBranch', 'editStartDate', 'editStartHour', 'editStartMinute', 'editEndDate', 'editEndHour', 'editEndMinute', 'editRemindMe', 'editCustomer', 'editCustomerName', 'editNotes']
                .forEach((key) => {
                    const el = els[key];
                    if (!el) return;
                    el.disabled = false;
                    if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                        el.readOnly = false;
                    }
                });
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

    if (els.branch) {
        els.branch.addEventListener('change', () => {
            // Keep Create modal branch aligned with the current filter (if applicable).
            if (els.createBranch) {
                els.createBranch.value = String(els.branch.value || '');
            }
            loadBookingsForRange();
        });
    }

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
        el.addEventListener('change', () => updateCreateVehicleSectionVisibility());
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
    updateOfflineNotice();
    window.addEventListener('online', updateOfflineNotice);
    window.addEventListener('offline', updateOfflineNotice);
    loadBookingsForRange();
})();
