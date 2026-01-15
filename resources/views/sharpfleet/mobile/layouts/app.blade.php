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
    <link rel="stylesheet" href="{{ asset('css/sharpfleet/sharpfleet-mobile.css?v=20260114-1') }}">
    <link rel="stylesheet" href="{{ asset('css/sharpfleet/sharpfleet-sheets.css?v=20260114-1') }}">

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
    navigator.serviceWorker.register('/sw.js');
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
            span.innerHTML = svg;
            el.replaceWith(span);
        });
    }

    window.sfSwapIcon = swapFallbackIcon;

    window.addEventListener('load', replaceIoniconsWithFallback);
})();
</script>

</body>
</html>
