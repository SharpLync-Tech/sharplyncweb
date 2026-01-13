<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'SharpFleet')</title>

    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0A2A4D">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Mobile-only CSS --}}
    <link rel="stylesheet" href="{{ asset('css/sharpfleet/sharpfleet-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sharpfleet/sharpfleet-sheets.css') }}">   
    @stack('styles')
</head>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
<body class="sf-mobile">

    {{-- App Shell --}}
    <div class="sf-mobile-app">

        {{-- Header --}}
        @include('sharpfleet.mobile.partials.header')

        {{-- Main Content --}}
        <main class="sf-mobile-content">
            @yield('content')
        </main>

        {{-- Footer --}}
        @include('sharpfleet.mobile.partials.footer')

    </div>

    {{-- Overlay backdrop for sheets --}}
    @include('sharpfleet.mobile.partials.overlays.backdrop')

    {{-- Mobile JS (later) --}}
    @stack('scripts')

</body>
</html>
