@extends('layouts.sharpfleet')

@section('title', 'Support')

@section('sharpfleet-content')
@php
    $redirectUrl = $redirectUrl ?? '/app/sharpfleet/driver';
@endphp

<div class="card" style="max-width: 820px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Feedback & Support</h2>
        <div class="hint-text">Tell us what happened or share feedback. It helps us improve SharpFleet.</div>
    </div>
    <div class="card-body">
        @if (session('success'))
            <div id="sfSupportSuccess" class="alert alert-success">
                <strong>Thanks, your message has been sent.</strong><br>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <strong>Could not send.</strong><br>
                {{ session('error') }}
            </div>
        @endif

        @if ($errors && $errors->any())
            <div class="alert alert-error">
                <strong>Please check the form.</strong><br>
                {{ $errors->first() }}
            </div>
        @endif

        <div id="sfSupportQueued" class="alert alert-info" style="display: none;">
            <strong>Queued for sending.</strong> We will send it when you are back online.
        </div>

        <form method="POST" action="/app/sharpfleet/support" id="sfSupportForm">
            @csrf

            <div class="form-group">
                <label class="form-label" for="sfSupportMessage">Send Feedback or Get Support</label>
                <textarea
                    id="sfSupportMessage"
                    name="message"
                    class="form-control"
                    rows="10"
                    maxlength="500"
                    required
                    placeholder="Tell us what happened or share feedback. Include any error you saw."
                >{{ old('message') }}</textarea>
                <div class="hint-text" id="sfSupportCounter">0 / 500</div>
            </div>

            <input type="hidden" name="platform" id="sfSupportPlatform">
            <input type="hidden" name="usage_mode" id="sfSupportUsageMode">
            <input type="hidden" name="client_timezone" id="sfSupportClientTimezone">
            <input type="hidden" name="logs" id="sfSupportLogs">

            <div class="hint-text" style="margin-bottom: 12px;">
                We will include device warnings/errors from the last 3 days to help debugging.
            </div>

            <button type="submit" class="btn btn-primary">Send Support Request</button>
        </form>
    </div>
</div>

<script>
(function () {
    const message = document.getElementById('sfSupportMessage');
    const counter = document.getElementById('sfSupportCounter');
    const platformField = document.getElementById('sfSupportPlatform');
    const modeField = document.getElementById('sfSupportUsageMode');
    const logsField = document.getElementById('sfSupportLogs');
    const timezoneField = document.getElementById('sfSupportClientTimezone');
    const queuedCard = document.getElementById('sfSupportQueued');
    const form = document.getElementById('sfSupportForm');
    const QUEUE_KEY = 'sf_support_queue_v1';
    const QUEUED_NOTICE_DELAY_MS = 1000;
    const redirectUrl = @json($redirectUrl);

    function updateCounter() {
        if (!message || !counter) return;
        const count = (message.value || '').length;
        counter.textContent = `${count} / 500`;
    }

    function detectPlatform() {
        const ua = (navigator.userAgent || '').toLowerCase();
        if (ua.includes('android')) return 'Android';
        if (ua.includes('iphone') || ua.includes('ipad') || ua.includes('ipod')) return 'Apple';
        return 'Other';
    }

    function detectUsageMode() {
        const isStandalone = window.matchMedia && window.matchMedia('(display-mode: standalone)').matches;
        const iosStandalone = window.navigator && window.navigator.standalone;
        return (isStandalone || iosStandalone) ? 'PWA' : 'Browser';
    }

    function detectTimezone() {
        try {
            return Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        } catch (e) {
            return '';
        }
    }

    function serializeLogs() {
        if (!window.sfGetLogs) return '';
        const logs = window.sfGetLogs();
        if (!Array.isArray(logs) || logs.length === 0) return '';
        try {
            return JSON.stringify(logs, null, 2);
        } catch (e) {
            return '';
        }
    }

    function getQueue() {
        try {
            const raw = localStorage.getItem(QUEUE_KEY);
            const parsed = raw ? JSON.parse(raw) : [];
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function setQueue(items) {
        try {
            localStorage.setItem(QUEUE_KEY, JSON.stringify(items));
        } catch (e) {
            // ignore storage errors
        }
    }

    function showQueued() {
        if (!queuedCard) return;
        queuedCard.style.display = '';
    }

    function buildQueuedPayload() {
        return {
            message: message ? String(message.value || '').trim() : '',
            platform: platformField ? String(platformField.value || '') : detectPlatform(),
            usage_mode: modeField ? String(modeField.value || '') : detectUsageMode(),
            client_timezone: timezoneField ? String(timezoneField.value || '') : detectTimezone(),
            logs: serializeLogs(),
            queuedAt: new Date().toISOString(),
        };
    }

    function buildFormData(item) {
        const fd = new FormData();
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fd.append('_token', token);
        fd.append('message', item.message || '');
        if (item.platform) fd.append('platform', item.platform);
        if (item.usage_mode) fd.append('usage_mode', item.usage_mode);
        if (item.client_timezone) fd.append('client_timezone', item.client_timezone);
        if (item.logs) fd.append('logs', item.logs);
        return fd;
    }

    async function sendQueuedRequest(item) {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 8000);
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
            const res = await fetch('/app/sharpfleet/support', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': token,
                },
                body: buildFormData(item),
                signal: controller.signal,
            });
            clearTimeout(timeout);
            return res && res.ok;
        } catch (e) {
            clearTimeout(timeout);
            return false;
        }
    }

    async function syncQueue() {
        if (!navigator.onLine) return;
        const queue = getQueue();
        if (queue.length === 0) return;

        const remaining = [];
        for (const item of queue) {
            const ok = await sendQueuedRequest(item);
            if (!ok) remaining.push(item);
        }
        setQueue(remaining);

        if (remaining.length === 0 && queuedCard) {
            queuedCard.style.display = 'none';
        }
    }

    updateCounter();
    if (platformField) platformField.value = detectPlatform();
    if (modeField) modeField.value = detectUsageMode();
    if (timezoneField) timezoneField.value = detectTimezone();

    if (message) {
        message.addEventListener('input', updateCounter);
    }

    if (form) {
        form.addEventListener('submit', (e) => {
            if (logsField) {
                logsField.value = serializeLogs();
            }

            if (navigator.onLine) {
                return;
            }

            e.preventDefault();

            const payload = buildQueuedPayload();
            if (window.sfQueueSupportRequest) {
                window.sfQueueSupportRequest(payload);
            } else {
                const queue = getQueue();
                queue.push(payload);
                setQueue(queue);
            }

            form.reset();
            updateCounter();
            if (platformField) platformField.value = detectPlatform();
            if (modeField) modeField.value = detectUsageMode();
            if (timezoneField) timezoneField.value = detectTimezone();
            showQueued();
            setTimeout(() => {
                window.location.href = redirectUrl;
            }, QUEUED_NOTICE_DELAY_MS);
        });
    }

    const existingQueue = getQueue();
    if (existingQueue.length > 0) {
        showQueued();
    }

    window.addEventListener('online', () => {
        syncQueue();
    });

    syncQueue();

    const successCard = document.getElementById('sfSupportSuccess');
    if (successCard) {
        if (!navigator.onLine) {
            successCard.style.display = 'none';
        } else {
            setTimeout(() => {
                successCard.style.transition = 'opacity 300ms ease';
                successCard.style.opacity = '0';
                setTimeout(() => {
                    successCard.style.display = 'none';
                    window.location.href = redirectUrl;
                }, 320);
            }, 4000);
        }
    }
})();
</script>
@endsection
