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
    <link rel="stylesheet" href="{{ asset('css/sharpfleet/sharpfleet-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sharpfleet/sharpfleet-sheets.css') }}">

    @stack('styles')
</head>

<body class="sf-mobile">

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

<div class="sf-mobile-app">

    @include('sharpfleet.mobile.partials.header')

    <main class="sf-mobile-content">
        @yield('content')
    </main>

    @include('sharpfleet.mobile.partials.footer')

</div>

@include('sharpfleet.mobile.partials.overlays.backdrop')

{{-- ===============================
     Sheet Controller
================================ --}}
<script>
(function () {
    const backdrop = document.getElementById('sf-sheet-backdrop');

    function openSheet(id) {
        const sheet = document.getElementById('sf-sheet-' + id);
        if (!sheet) return;

        sheet.classList.add('is-open');
        sheet.setAttribute('aria-hidden', 'false');
        backdrop.style.display = 'block';
    }

    function closeSheets() {
        document.querySelectorAll('.sf-sheet.is-open').forEach(sheet => {
            sheet.classList.remove('is-open');
            sheet.setAttribute('aria-hidden', 'true');
        });
        backdrop.style.display = 'none';
    }

    document.addEventListener('click', (e) => {
        const openBtn = e.target.closest('[data-sheet-open]');
        const closeBtn = e.target.closest('[data-sheet-close]');

        if (openBtn) {
            openSheet(openBtn.dataset.sheetOpen);
        }

        if (closeBtn || e.target === backdrop) {
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
