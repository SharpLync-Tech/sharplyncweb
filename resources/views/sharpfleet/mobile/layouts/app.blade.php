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

{{-- Ionicons --}}
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

<div class="sf-mobile-app">

    {{-- Header --}}
    @include('sharpfleet.mobile.partials.header')

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
            openSheet(openBtn.dataset.sheetOpen);
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

</body>
</html>
