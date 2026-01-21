<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'SharpFleet Reports')</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Core SharpFleet base styles (fonts, colours, nav) --}}
    <link rel="stylesheet" href="{{ asset('css/sharpfleet/sharpfleet.css') }}">

    {{-- Reports-only overrides --}}
    <link rel="stylesheet" href="{{ asset('css/sharpfleet/sharpfleet-reports.css') }}">

    @stack('styles')
</head>

<body class="sf-reports-body">

    {{-- NAV ONLY – no layout, no containers, no footer --}}
    @include('layouts.sharpfleet-nav')

    {{-- REPORT CONTENT – starts immediately after nav --}}
    <main class="sf-reports-main">
        @yield('content')
    </main>

    {{-- Reports-only footer --}}
    @include('layouts.sharpfleet-reports-footer')

    @stack('scripts')
</body>
</html>
