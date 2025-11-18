{{-- 
  Layout: layouts/services/services-base.blade.php
  Version: v1.1 (Extends main site layout + Services Hero)
  Description:
  - Reuses main header/footer from layouts.base
  - Adds a SharpLync-style hero specific to Services
  - Provides @yield('services-content') for pages like services.mock
--}}

@extends('layouts.base')

@section('title', 'SharpLync | Services')

@push('styles')
    {{-- Services-specific stylesheet (hero styling still from sharplync.css) --}}
    <link rel="stylesheet" href="{{ secure_asset('css/services/services.css') }}">
@endpush

@section('content')
    <!-- ======= FULL SHARPLYNC HERO (Same style as home, but for Services) ======= -->
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

    <!-- ======= SERVICES PAGE CONTENT WRAPPER ======= -->
    <main class="services-content">
        @yield('services-content')
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/services/services.js') }}"></script>
@endpush
