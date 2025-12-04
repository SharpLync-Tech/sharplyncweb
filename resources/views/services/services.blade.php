{{-- resources/views/services/services.blade.php --}}
@extends('layouts.base')

@section('title', 'SharpLync | Services')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/services/services.css') }}?v=7001">
@endpush

@section('content')
<section class="services-root services-hero">

    {{-- CPU BG --}}
    <div class="hero-cpu-bg">
        <img src="{{ asset('images/hero-cpu.png') }}" alt="CPU">
    </div>

    {{-- LOGO --}}
    <img src="{{ asset('images/sharplync-logo.png') }}" class="hero-logo">

    {{-- HEADING --}}
    <div class="hero-text">
        <h1>Your Business,<br><span class="highlight">Secure & Connected.</span></h1>
    </div>

    {{-- SERVICE GRID --}}
    <div id="servicesGrid" class="services-cards">
        @foreach ($categories as $cat)
        <div class="service-tile"
             data-title="{{ $cat['title'] }}"
             data-short="{{ $cat['short'] }}"
             data-long="{{ $cat['long'] }}"
             data-icon="{{ asset($cat['icon']) }}"
             data-image="{{ asset($cat['image']) }}"
             data-subs='@json($cat["subs"])'
             @if(isset($cat["partner_badge"]))
                data-partner-logo="{{ $cat['partner_badge']['logo'] }}"
                data-partner-title="{{ $cat['partner_badge']['title'] }}"
                data-partner-text="{{ $cat['partner_badge']['text'] }}"
             @endif
        >

            <div class="tile-header">
                <img src="{{ asset($cat['icon']) }}" class="tile-icon">
                <h3>{{ $cat['title'] }}</h3>
                <p>{{ $cat['short'] }}</p>
                <button class="tile-toggle">Learn More</button>
            </div>

        </div>
        @endforeach
    </div>

    {{-- EXPANDED ONE-CARD CLEAN UI --}}
    <div id="expandedService" class="mock-wrapper">

        <div class="mock-header">
            <img id="expIcon" src="">
            <h2 id="expTitle"></h2>
            <p id="expShort"></p>
            <button id="closeExpanded" class="mock-close-btn">Close</button>
            <a href="https://sharplync.com.au/contact" class="mock-cta-btn">Let’s Get You Sorted</a>
        </div>

        <hr class="mock-divider">

        <div class="mock-content">
            <div class="mock-image">
                <img id="expImage" src="">
            </div>

            <div class="mock-text">
                <p id="expLong"></p>

                {{-- TREND MICRO PARTNER BADGE – Same style as TM hero --}}
                <a href="{{ url('/trend-micro') }}" id="partnerBadge" style="display:none;">
                    <div class="tm-partner-badge">
                        <div class="tm-logo-wrap">
                            <img id="partnerBadgeLogo"
                                 src=""
                                 alt="Trend Micro Partner Logo">
                        </div>
                        <div class="tm-badge-text">
                            <span id="partnerBadgeTitle" class="tm-badge-title">
                                Official Trend Micro Partner
                            </span>
                            <span id="partnerBadgeText" class="tm-badge-note">
                                Powered by the Trend Micro Vision One™ security platform.
                            </span>
                        </div>
                    </div>
                </a>

                <h4>Included Services</h4>
                <ul id="expSubs"></ul>
            </div>
        </div>

    </div>

</section>
@endsection

@push('scripts')
<script src="{{ asset('js/services/services.js') }}?v=9001"></script>
@endpush
