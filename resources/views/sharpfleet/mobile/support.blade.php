@extends('sharpfleet.mobile.layouts.app')

@section('title', 'Support')

@section('content')
@php
    $driverName = trim((string) (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
    $driverEmail = trim((string) ($user['email'] ?? ''));
@endphp

<section class="sf-mobile-dashboard">
    <h1 class="sf-mobile-title">Feedback &Support</h1>
    <p class="sf-mobile-subtitle" style="margin-top: -6px; margin-bottom: 14px;">
        Tell us what happened or share feedback, it helps us improve SharpFleet.
    </p>

    @if (session('success'))
        <div id="sfSupportSuccess" class="sf-mobile-card" style="margin-bottom: 16px;">
            <div class="sf-mobile-card-title">Thanks, your message has been sent.</div>
            <div class="sf-mobile-card-text">{{ session('success') }}</div>
        </div>
    @endif

    @if (session('error'))
        <div class="sf-mobile-card" style="margin-bottom: 16px;">
            <div class="sf-mobile-card-title">Could not send</div>
            <div class="sf-mobile-card-text">{{ session('error') }}</div>
        </div>
    @endif

    @if ($errors && $errors->any())
        <div class="sf-mobile-card" style="margin-bottom: 16px;">
            <div class="sf-mobile-card-title">Please check the form</div>
            <div class="sf-mobile-card-text">{{ $errors->first() }}</div>
        </div>
    @endif

    <div id="sfSupportQueued" class="sf-mobile-card" style="margin-bottom: 16px; display: none;">
        <div class="sf-mobile-card-title">Queued for sending</div>
        <div class="sf-mobile-card-text">Queued, will send when online.</div>
    </div>

    <form method="POST" action="/app/sharpfleet/mobile/support" id="sfSupportForm" class="sf-support-form">
        @csrf

        <div class="form-group sf-support-group">
            <label class="form-label">Send Feedback or Get Support</label>
            <textarea
                id="sfSupportMessage"
                name="message"
                class="form-control sf-support-textarea"
                rows="13"
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

        <button type="submit" class="sf-mobile-primary-btn">
            Send Support Request
        </button>
    </form>
</section>

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
                window.location.href = '/app/sharpfleet/mobile';
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
                window.location.href = '/app/sharpfleet/mobile';
            }, 320);
        }, 4000);
        }
    }
})();
</script>
@endsection
