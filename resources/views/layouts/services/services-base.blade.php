<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync | Services')</title>

    {{-- Use the main SharpLync stylesheet for hero + branding --}}
    <link rel="stylesheet" href="{{ secure_asset('css/sharplync.css') }}">

    {{-- Services-specific stylesheet --}}
    <link rel="stylesheet" href="/css/services/services.css">

    @stack('styles')
</head>

<body class="services-root">

    <!-- ======= FULL SHARPLYNC HERO (Same style as home) ======= -->
    <header class="hero services-hero">

        <!-- CPU Background -->
        <div class="hero-cpu-bg">
            <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
        </div>

        <!-- Logo -->
        <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo" class="hero-logo">

        <!-- Hero Text -->
        <div class="hero-text">
            <h1>
                What We Do<br>
                <span class="highlight">Sharp Solutions</span>
            </h1>
            <p>
                From the Granite Belt to the Cloud — smart systems, secure solutions,
                and real people who get IT right.
            </p>
        </div>

    </header>

    <!-- ======= PAGE CONTENT ======= -->
    <main class="services-content">
        @yield('content')
    </main>

    <!-- ======= SERVICES JS ======= -->
    <script src="/js/services/services.js"></script>
    @stack('scripts')
</body>
</html>
