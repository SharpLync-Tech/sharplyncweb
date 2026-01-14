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

    {{-- Apple PWA polish --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SharpFleet">
    <link rel="apple-touch-icon" href="/images/sharpfleet/pwa/icon-192.png">

    {{-- Mobile-only CSS --}}
    <link rel="stylesheet" href="{{ asset('css/sharpfleet/sharpfleet-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sharpfleet/sharpfleet-sheets.css') }}">

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

    {{-- Backdrop (future sheets) --}}
    @include('sharpfleet.mobile.partials.overlays.backdrop')

    @stack('scripts')

    {{-- ===============================
         Service Worker Registration
    ================================ --}}
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>

    {{-- ===============================
         PWA Install Logic (Chrome/Edge)
    ================================ --}}
    <script>
        window.deferredPrompt = null;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.deferredPrompt = e;

            const btn = document.getElementById('pwa-install-btn');
            if (btn) btn.style.display = 'flex';
        });

        async function installPWA() {
            if (!window.deferredPrompt) {
                alert('Install not available yet');
                return;
            }

            window.deferredPrompt.prompt();
            await window.deferredPrompt.userChoice;
            window.deferredPrompt = null;

            const btn = document.getElementById('pwa-install-btn');
            if (btn) btn.style.display = 'none';
        }
    </script>

    {{-- ===============================
         iOS Install Hint
    ================================ --}}
    <script>
        (function () {
            const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
            const isStandalone = window.navigator.standalone === true;

            if (isIos && !isStandalone) {
                const hint = document.getElementById('ios-install-hint');
                if (hint) hint.style.display = 'block';
            }
        })();
    </script>

</body>
</html>
