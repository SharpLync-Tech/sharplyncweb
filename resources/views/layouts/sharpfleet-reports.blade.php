<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'SharpFleet Reports')</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Core SharpFleet branding --}}
    <link rel="stylesheet" href="{{ asset('css/sharpfleet/sharpfleet.css') }}">

    {{-- Reports-only stylesheet --}}
    <link rel="stylesheet" href="{{ asset('css/sharpfleet/sharpfleet-reports.css') }}">

    @stack('styles')
</head>
<body class="sf-reports-body">

    {{-- KEEP existing top nav for consistency --}}
    @include('partials.sharpfleet-header')

    {{-- REPORT CONTENT (no containers, no constraints) --}}
    <main class="sf-reports-main">
        @yield('content')
    </main>

    {{-- Reports-only footer --}}
    @include('layouts.sharpfleet-reports-footer')

    @stack('scripts')
</body>
</html>
