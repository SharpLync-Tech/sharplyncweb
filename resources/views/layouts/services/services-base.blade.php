@extends('layouts.base')

@section('title', 'SharpLync | Services')

@push('styles')
<link rel="stylesheet" href="{{ secure_asset('css/services/services.css') }}">
@endpush

@section('content')

    <!-- ======= SERVICES HERO (FULL SHARPLYNC STYLE) ======= -->
    <header class="hero services-hero">
        <div class="hero-cpu-bg">
            <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
        </div>

        <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo" class="hero-logo">

        <div class="hero-text">
            <h1>
                What We Do<br>
                <span class="highlight">Sharp Solutions</span>
            </h1>            
        </div>
    </header>

    <!-- ======= SERVICES PAGE CONTENT ======= -->
    <main class="services-content">
        @yield('services-content')
    </main>

@endsection

{{-- MUST be outside @section to correctly push to base layout --}}
@push('scripts')
<script src="{{ asset('js/services/services.js') }}"></script>
@endpush
