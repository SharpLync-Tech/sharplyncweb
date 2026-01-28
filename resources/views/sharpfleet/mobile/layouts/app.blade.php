<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'SharpFleet')</title>

    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0A2A4D">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">

    {{-- Apple PWA --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SharpFleet">
    <link rel="apple-touch-icon" href="/images/sharpfleet/pwa/icon-192.png">

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('css/sharpfleet/sharpfleet-mobile.css?v=20260128-1') }}">
    <link rel="stylesheet" href="{{ asset('css/sharpfleet/sharpfleet-sheets.css?v=20260128-1') }}">

    @stack('styles')
</head>

<body class="sf-mobile">

{{-- Ionicons (fallback handled below for offline) --}}
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

<div class="sf-mobile-app">

    {{-- Header --}}
    @include('sharpfleet.mobile.partials.header')

    {{-- Offline Banner --}}
    <div id="sf-offline-banner" class="sf-mobile-offline-banner" style="display:none;">
        You are currently working offline
    </div>

    {{-- Main --}}
    <main class="sf-mobile-content">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('sharpfleet.mobile.partials.footer')

</div>

{{-- Global Backdrop --}}
@include('sharpfleet.mobile.partials.overlays.backdrop')

{{-- ===============================
     Mobile Token Bootstrap
================================ --}}
<script>
(function () {
    const DEVICE_ID_KEY = 'sf_device_id';
    const TOKEN_KEY = 'sf_device_token';
    const COOKIE_MAX_DAYS = 30;

    function setCookie(name, value, days) {
        if (!value) return;
        const maxAge = Math.max(1, days || COOKIE_MAX_DAYS) * 24 * 60 * 60;
        let cookie = name + '=' + encodeURIComponent(value)
            + '; path=/app/sharpfleet'
            + '; max-age=' + maxAge
            + '; samesite=lax';
        if (window.location && window.location.protocol === 'https:') {
            cookie += '; secure';
        }
        document.cookie = cookie;
    }

    function getDeviceId() {
        try {
            return localStorage.getItem(DEVICE_ID_KEY) || '';
        } catch (e) {
            return '';
        }
    }

    function setDeviceId(value) {
        if (!value) return;
        try {
            localStorage.setItem(DEVICE_ID_KEY, value);
        } catch (e) {
            // ignore storage errors
        }
        setCookie('sf_device_id', value, COOKIE_MAX_DAYS);
    }

    function getToken() {
        try {
            return localStorage.getItem(TOKEN_KEY) || '';
        } catch (e) {
            return '';
        }
    }

    function setToken(value) {
        if (!value) return;
        try {
            localStorage.setItem(TOKEN_KEY, value);
        } catch (e) {
            // ignore storage errors
        }
        setCookie('sf_device_token', value, COOKIE_MAX_DAYS);
    }

    let deviceId = getDeviceId();
    if (!deviceId) {
        deviceId = (typeof crypto !== 'undefined' && crypto.randomUUID)
            ? crypto.randomUUID()
            : 'sf-' + Math.random().toString(16).slice(2) + Date.now().toString(16);
        setDeviceId(deviceId);
    }

    const serverToken = '{{ (string) session('sharpfleet.mobile_token', '') }}';
    const serverDeviceId = '{{ (string) session('sharpfleet.mobile_device_id', '') }}';
    if (serverDeviceId && serverDeviceId !== deviceId) {
        setDeviceId(serverDeviceId);
        deviceId = serverDeviceId;
    }
    if (serverToken) {
        setToken(serverToken);
    }

    window.SharpFleetMobileAuth = {
        getToken,
        getDeviceId,
    };

    const originalFetch = window.fetch.bind(window);
    window.fetch = function (input, init) {
        const req = typeof input === 'string' ? { url: input } : { url: input.url || '' };
        const url = req.url || '';
        const isSameOrigin = url.startsWith('/') || url.startsWith(window.location.origin);
        const isSharpFleet = url.includes('/app/sharpfleet/');
        const token = getToken();
        const device = getDeviceId();

        if (isSameOrigin && isSharpFleet) {
            init = init || {};
            init.headers = init.headers || {};
            if (init.headers instanceof Headers) {
                if (token && !init.headers.has('Authorization')) {
                    init.headers.set('Authorization', 'Bearer ' + token);
                }
                if (!init.headers.has('X-Device-Id') && device) {
                    init.headers.set('X-Device-Id', device);
                }
                if (!token && !init.headers.has('X-Device-Token')) {
                    init.headers.set('X-Device-Token', 'missing');
                }
            } else {
                if (token && !('Authorization' in init.headers)) {
                    init.headers['Authorization'] = 'Bearer ' + token;
                }
                if (!('X-Device-Id' in init.headers) && device) {
                    init.headers['X-Device-Id'] = device;
                }
                if (!token && !('X-Device-Token' in init.headers)) {
                    init.headers['X-Device-Token'] = 'missing';
                }
            }
        }

        return originalFetch(input, init);
    };

    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!form || !form.matches('[data-mobile-token-form]')) return;
        if (event.defaultPrevented) return;

        const token = getToken();
        const device = getDeviceId();
        event.preventDefault();

        const formData = new FormData(form);
        if (device) {
            formData.set('device_id', device);
        }

        if (!navigator.onLine && typeof window.sfHandleOfflineTripSubmit === 'function') {
            try {
                const handled = await window.sfHandleOfflineTripSubmit(form, formData);
                if (handled) return;
            } catch (e) {
                // fall through to fetch if offline handler fails
            }
        }

        let controller = null;
        let timeoutId = null;
        try {
            if (typeof AbortController !== 'undefined') {
                controller = new AbortController();
                timeoutId = setTimeout(() => controller.abort(), 6000);
            }

            const res = await fetch(form.action, {
                method: form.method || 'POST',
                credentials: 'same-origin',
                headers: {
                    ...(token ? { 'Authorization': 'Bearer ' + token } : { 'X-Device-Token': 'missing' }),
                    'X-Device-Id': device || '',
                    'Accept': 'application/json',
                },
                body: formData,
                signal: controller ? controller.signal : undefined,
            });
            if (timeoutId) clearTimeout(timeoutId);

            if (res.status === 422) {
                let errors = [];
                try {
                    const data = await res.json();
                    if (data && data.errors) {
                        Object.values(data.errors).forEach(list => {
                            if (Array.isArray(list)) {
                                list.forEach(msg => errors.push(String(msg)));
                            }
                        });
                    }
                    if (errors.length === 0 && data && data.message) {
                        errors.push(String(data.message));
                    }
                } catch (e) {
                    errors = [];
                }
                if (errors.length === 0) {
                    errors = ['Please complete the required fields.'];
                }

                if (form.id === 'startTripForm') {
                    const sheet = document.getElementById('sf-sheet-start-trip');
                    const backdrop = document.getElementById('sf-sheet-backdrop');
                    if (sheet) {
                        sheet.classList.add('is-open');
                        sheet.setAttribute('aria-hidden', 'false');
                    }
                    if (backdrop) backdrop.style.display = 'block';
                    document.body.style.overflow = 'hidden';

                    const modal = document.getElementById('sf-mobile-validation-modal');
                    const list = document.getElementById('sf-mobile-validation-list');
                    if (modal && list) {
                        list.innerHTML = '';
                        errors.forEach(item => {
                            const li = document.createElement('li');
                            li.textContent = item;
                            list.appendChild(li);
                        });
                        modal.classList.add('is-open');
                        modal.setAttribute('aria-hidden', 'false');
                        modal.hidden = false;
                    }
                    return;
                }
            }

            if (res.status === 202) {
                const alert = document.getElementById('offlineTripAlert');
                if (alert) {
                    alert.textContent = 'Trip saved offline and will sync when you are back online.';
                    alert.style.display = '';
                }
                return;
            }

            if (res.redirected) {
                window.location.href = res.url;
                return;
            }

            window.location.reload();
        } catch (e) {
            if (timeoutId) clearTimeout(timeoutId);
            if (typeof window.sfHandleOfflineTripSubmit === 'function') {
                try {
                    const handled = await window.sfHandleOfflineTripSubmit(form, formData, { force: true });
                    if (handled) return;
                } catch (err) {
                    // ignore and fall back to generic message
                }
            }
            const alert = document.getElementById('offlineTripAlert');
            if (alert) {
                alert.textContent = (e && e.name === 'AbortError')
                    ? 'Network timeout. If you are offline, the trip will sync when you are back online.'
                    : 'Network error. Please try again when you are back online.';
                alert.style.display = '';
            }
        }
    });
})();
</script>

{{-- ===============================
     Sheet Controller (GLOBAL)
================================ --}}
<script>
(function () {
    const backdrop = document.getElementById('sf-sheet-backdrop');

    function openSheet(id) {
        const sheet = document.getElementById('sf-sheet-' + id);
        if (!sheet || !backdrop) return;

        sheet.classList.add('is-open');
        sheet.setAttribute('aria-hidden', 'false');

        backdrop.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeSheet(sheet) {
        if (!sheet) return;
        sheet.classList.remove('is-open');
        sheet.setAttribute('aria-hidden', 'true');
    }

    function closeSheets() {
        document.querySelectorAll('.sf-sheet.is-open').forEach(sheet => {
            closeSheet(sheet);
        });

        if (backdrop) backdrop.style.display = 'none';
        document.body.style.overflow = '';
    }

    function syncBackdrop() {
        const hasOpen = document.querySelector('.sf-sheet.is-open');
        if (hasOpen) return;
        if (backdrop) backdrop.style.display = 'none';
        document.body.style.overflow = '';
    }

    document.addEventListener('click', (e) => {
        const openBtn = e.target.closest('[data-sheet-open]');
        const closeBtn = e.target.closest('[data-sheet-close]');

        if (openBtn) {
            const sheetId = openBtn.dataset.sheetOpen;
            const sheet = document.getElementById('sf-sheet-' + sheetId);

            if (sheet) {
                e.preventDefault();
                openSheet(sheetId);
            }
        }

        if (closeBtn) {
            if (closeBtn.dataset.sheetClose === 'self') {
                closeSheet(closeBtn.closest('.sf-sheet'));
                syncBackdrop();
            } else {
                closeSheets();
            }
        }

        if (e.target === backdrop) {
            closeSheets();
        }
    });
})();
</script>

{{-- Service Worker --}}
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').then((reg) => {
        if (reg && reg.update) {
            reg.update();
        }
    }).catch(() => {
        // ignore registration errors
    });
}
</script>

{{-- Offline banner + icon fallback --}}
<script>
(function () {
    const banner = document.getElementById('sf-offline-banner');

    function updateBanner() {
        if (!banner) return;
        banner.style.display = navigator.onLine ? 'none' : 'block';
    }

    window.addEventListener('online', updateBanner);
    window.addEventListener('offline', updateBanner);
    updateBanner();

    function iconSvg(name) {
        const stroke = 'currentColor';
        const common = 'fill="none" stroke="' + stroke + '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';

        const icons = {
            'close-outline': '<svg viewBox="0 0 24 24" aria-hidden="true"><line x1="6" y1="6" x2="18" y2="18" ' + common + '></line><line x1="18" y1="6" x2="6" y2="18" ' + common + '></line></svg>',
            'menu-outline': '<svg viewBox="0 0 24 24" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6" ' + common + '></line><line x1="3" y1="12" x2="21" y2="12" ' + common + '></line><line x1="3" y1="18" x2="21" y2="18" ' + common + '></line></svg>',
            'home-outline': '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10.5L12 3l9 7.5" ' + common + '></path><path d="M5 10.5V20h14v-9.5" ' + common + '></path></svg>',
            'play-circle-outline': '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" ' + common + '></circle><polygon points="10,8 17,12 10,16" fill="currentColor"></polygon></svg>',
            'time-outline': '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" ' + common + '></circle><line x1="12" y1="7" x2="12" y2="12" ' + common + '></line><line x1="12" y1="12" x2="16" y2="14" ' + common + '></line></svg>',
            'ellipse-outline': '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="6" ' + common + '></circle></svg>',
            'checkmark': '<svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="5 13 9 17 19 7" ' + common + '></polyline></svg>',
        };

        return icons[name] || '';
    }

    function swapFallbackIcon(el, name) {
        if (!el) return;
        const tag = (el.tagName || '').toLowerCase();
        if (tag === 'ion-icon') {
            el.setAttribute('name', name);
            return;
        }
        if (el.classList && el.classList.contains('sf-icon-fallback')) {
            const svg = iconSvg(name);
            if (svg) el.innerHTML = svg;
        }
    }

    function replaceIoniconsWithFallback() {
        if (customElements.get('ion-icon')) return;
        document.querySelectorAll('ion-icon').forEach(el => {
            const name = el.getAttribute('name') || '';
            const svg = iconSvg(name);
            if (!svg) return;
            const span = document.createElement('span');
            span.className = 'sf-icon-fallback ' + (el.getAttribute('class') || '');
            Array.from(el.attributes).forEach(attr => {
                if (attr.name === 'class' || attr.name === 'name') return;
                span.setAttribute(attr.name, attr.value);
            });
            span.innerHTML = svg;
            el.replaceWith(span);
        });
    }

    window.sfSwapIcon = swapFallbackIcon;

    window.addEventListener('load', replaceIoniconsWithFallback);
})();
</script>

{{-- Client-side logs (warnings/errors only; local storage, 3 days / 100 entries) --}}
<script>
(function () {
    const LOG_KEY = 'sf_mobile_logs_v1';
    const MAX_ENTRIES = 100;
    const MAX_AGE_MS = 3 * 24 * 60 * 60 * 1000;
    let memoryLogs = [];

    function safeParse(raw) {
        try { return JSON.parse(raw); } catch (e) { return []; }
    }

    function prune(logs) {
        const cutoff = Date.now() - MAX_AGE_MS;
        const filtered = (Array.isArray(logs) ? logs : []).filter(item => {
            const ts = item && item.ts ? Date.parse(item.ts) : NaN;
            return Number.isFinite(ts) && ts >= cutoff;
        });
        return filtered.slice(-MAX_ENTRIES);
    }

    function persist(logs) {
        try {
            localStorage.setItem(LOG_KEY, JSON.stringify(logs));
        } catch (e) {
            // ignore storage errors
        }
    }

    function load() {
        let logs = [];
        try {
            const raw = localStorage.getItem(LOG_KEY);
            logs = safeParse(raw || '[]');
        } catch (e) {
            logs = [];
        }
        logs = prune(logs);
        memoryLogs = logs.slice();
        persist(logs);
    }

    function normalizeMessage(message) {
        if (typeof message === 'string') return message.slice(0, 500);
        try {
            return JSON.stringify(message).slice(0, 500);
        } catch (e) {
            return 'Unserializable message';
        }
    }

    function addLog(level, message, context) {
        if (level !== 'warning' && level !== 'error') return;
        const entry = {
            ts: new Date().toISOString(),
            level,
            message: normalizeMessage(message),
            context: context || null,
        };
        memoryLogs.push(entry);
        const pruned = prune(memoryLogs);
        memoryLogs = pruned.slice();
        persist(pruned);
    }

    window.sfLog = addLog;
    window.sfGetLogs = function () {
        return prune(memoryLogs.slice());
    };

    load();

    window.addEventListener('error', (event) => {
        addLog('error', event.message || 'Unhandled error', {
            source: event.filename || null,
            line: event.lineno || null,
            column: event.colno || null,
        });
    });

    window.addEventListener('unhandledrejection', (event) => {
        const reason = event && event.reason ? event.reason : 'Unhandled promise rejection';
        addLog('error', reason && reason.message ? reason.message : reason, {
            source: 'promise',
        });
    });
})();
</script>

{{-- Support request queue (local storage) --}}
<script>
(function () {
    const SUPPORT_QUEUE_KEY = 'sf_support_queue_v1';
    const SUPPORT_SENT_KEY = 'sf_support_sent_notice_v1';

    function getQueue() {
        try {
            const raw = localStorage.getItem(SUPPORT_QUEUE_KEY);
            const parsed = raw ? JSON.parse(raw) : [];
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function setQueue(items) {
        try {
            localStorage.setItem(SUPPORT_QUEUE_KEY, JSON.stringify(items));
        } catch (e) {
            // ignore storage errors
        }
    }

    function buildFormData(item) {
        const fd = new FormData();
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fd.append('_token', token);
        fd.append('message', item.message || '');
        if (item.app_version) fd.append('app_version', item.app_version);
        if (item.platform) fd.append('platform', item.platform);
        if (item.usage_mode) fd.append('usage_mode', item.usage_mode);
        if (item.client_timezone) fd.append('client_timezone', item.client_timezone);
        if (item.page_url) fd.append('page_url', item.page_url);
        if (item.device_id) fd.append('device_id', item.device_id);
        if (item.logs) fd.append('logs', item.logs);
        return fd;
    }

    async function sendQueuedRequest(item) {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 8000);
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
            const res = await fetch('/app/sharpfleet/mobile/support', {
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
        let sentAny = false;
        for (const item of queue) {
            const ok = await sendQueuedRequest(item);
            if (!ok) {
                remaining.push(item);
            } else {
                sentAny = true;
            }
        }
        setQueue(remaining);

        if (sentAny && remaining.length === 0) {
            try {
                localStorage.setItem(SUPPORT_SENT_KEY, new Date().toISOString());
            } catch (e) {
                // ignore storage errors
            }
        }
    }

    window.sfQueueSupportRequest = function (payload) {
        const queue = getQueue();
        queue.push(payload);
        setQueue(queue);
    };

    window.sfSyncSupportQueue = syncQueue;

    window.addEventListener('online', () => {
        syncQueue();
    });

    syncQueue();
})();
</script>

</body>
</html>
