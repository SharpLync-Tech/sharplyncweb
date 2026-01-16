@extends('sharpfleet.mobile.layouts.app')

@section('title', 'Support')

@section('content')
@php
    $driverName = trim((string) (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
    $driverEmail = trim((string) ($user['email'] ?? ''));
@endphp

<section class="sf-mobile-dashboard">
    <h1 class="sf-mobile-title">Support</h1>
    <p class="sf-mobile-subtitle" style="margin-top: -6px; margin-bottom: 20px;">
        Tell us what happened and we will help.
    </p>

    @if (session('success'))
        <div id="sfSupportSuccess" class="sf-mobile-card" style="margin-bottom: 16px;">
            <div class="sf-mobile-card-title">Request sent</div>
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

    <form method="POST" action="/app/sharpfleet/mobile/support" id="sfSupportForm" class="sf-support-form">
        @csrf

        <div class="form-group sf-support-group">
            <label class="form-label">Support request</label>
            <textarea
                id="sfSupportMessage"
                name="message"
                class="form-control sf-support-textarea"
                rows="12"
                maxlength="500"
                required
                placeholder="Describe the issue, what you were trying to do, and any error you saw."
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
    const form = document.getElementById('sfSupportForm');

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

    updateCounter();
    if (platformField) platformField.value = detectPlatform();
    if (modeField) modeField.value = detectUsageMode();
    if (timezoneField) timezoneField.value = detectTimezone();

    if (message) {
        message.addEventListener('input', updateCounter);
    }

    if (form) {
        form.addEventListener('submit', () => {
            if (logsField) {
                logsField.value = serializeLogs();
            }
        });
    }

    const successCard = document.getElementById('sfSupportSuccess');
    if (successCard) {
        setTimeout(() => {
            successCard.style.transition = 'opacity 300ms ease';
            successCard.style.opacity = '0';
            setTimeout(() => {
                successCard.style.display = 'none';
            }, 320);
        }, 4000);
    }
})();
</script>
@endsection
