{{-- resources/views/services/mock.blade.php --}}
@extends('layouts.base')

@section('title', 'SharpLync | Services')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/services/services.css') }}?v=9001">
@endpush

@section('content')
<section class="hero services-hero">

    {{-- CPU background --}}
    <div class="hero-cpu-bg">
        <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
    </div>

    {{-- Logo --}}
    <img src="{{ asset('images/sharplync-logo.png') }}"
         alt="SharpLync Hero Logo"
         class="hero-logo">

    {{-- Hero Heading --}}
    <div class="hero-text">
        <h1>Your Business,<br>
            <span class="highlight">Secure & Connected.</span>
        </h1>
    </div>

    {{-- GRID OF SERVICE TILES --}}
    <div class="hero-cards fade-section services-cards" id="servicesGrid">

        @foreach ($categories as $cat)
        <div class="tile service-tile" data-id="{{ $cat['id'] }}" data-title="{{ $cat['title'] }}"
             data-short="{{ $cat['short'] }}"
             data-long="{{ $cat['long'] }}"
             data-icon="{{ asset($cat['icon']) }}"
             data-image="{{ asset($cat['image']) }}"
             data-subs="{{ json_encode($cat['subs']) }}">

            <div class="tile-header">
                <img src="{{ asset($cat['icon']) }}" class="tile-icon">
                <h3>{{ $cat['title'] }}</h3>
                <p>{{ $cat['short'] }}</p>

                <button type="button" class="tile-toggle">Learn More</button>
            </div>

        </div>
        @endforeach

    </div>


    {{-- THE EXPANDED FULL-WIDTH CLEAN LAYOUT --}}
    <div id="expandedService" style="display:none;">

        <div class="mock-wrapper">

            <div class="mock-header">
                <img id="expIcon" src="" alt="">
                <h2 id="expTitle"></h2>
                <p id="expShort"></p>

                <button class="mock-close-btn" id="closeExpanded">Close</button>
            </div>

            <hr class="mock-divider">

            <div class="mock-content">
                <div class="mock-image">
                    <img id="expImage" src="" alt="">
                </div>

                <div class="mock-text">
                    <p id="expLong"></p>

                    <h4>Included Services</h4>
                    <ul id="expSubs"></ul>
                </div>
            </div>

        </div>

    </div>

</section>
@endsection

@push('scripts')
<script src="{{ asset('js/services/services.js') }}?v=9001" defer></script>
@endpush
