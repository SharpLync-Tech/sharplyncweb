{{-- resources/views/services/mock.blade.php --}}
@extends('layouts.base')

@section('title', 'SharpLync | Services')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/services/services.css') }}?v=6969">
@endpush

@section('content')
<main class="services-root">

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

        {{-- ===== MAIN GRID OF SERVICE CARDS ===== --}}
        <div id="servicesGrid" class="hero-cards fade-section services-cards">

            @foreach ($categories as $cat)
                <div class="tile service-tile"
                     data-title="{{ $cat['title'] }}"
                     data-short="{{ $cat['short'] }}"
                     data-long="{{ $cat['long'] }}"
                     data-icon="{{ asset($cat['icon']) }}"
                     data-image="{{ asset($cat['image']) }}"
                     data-subs='@json($cat['subs'] ?? [])'>

                    {{-- TILE HEADER --}}
                    <div class="tile-header">
                        @if (!empty($cat['icon']))
                            <img src="{{ asset($cat['icon']) }}"
                                 alt="{{ $cat['title'] }} Icon"
                                 class="tile-icon">
                        @endif

                        <h3>{{ $cat['title'] }}</h3>
                        <p>{{ $cat['short'] }}</p>

                        <button type="button" class="tile-toggle">
                            Learn More
                        </button>
                    </div>

                    {{-- Collapsed detail area (kept for structure, but not used for mock layout) --}}
                    <div class="tile-detail">
                        <hr class="tile-divider">
                        <div class="tile-detail-inner">
                            <div class="detail-image-wrapper">
                                @if (!empty($cat['image']))
                                    <img src="{{ asset($cat['image']) }}"
                                         alt="{{ $cat['title'] }} Image"
                                         class="detail-image">
                                @endif
                            </div>
                            <div class="tile-detail-content tile-detail-content--centered">
                                <p>{{ $cat['long'] }}</p>

                                @if (!empty($cat['subs']))
                                    <h4>Included Services</h4>
                                    <ul class="detail-list">
                                        @foreach ($cat['subs'] as $sub)
                                            <li>{{ $sub }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            @endforeach

        </div> {{-- /#servicesGrid --}}

        {{-- Anchor so we can park the expanded card here when closing --}}
        <div id="expandedAnchor"></div>

        {{-- ===== EXPANDED MOCK-CLEAN CARD ===== --}}
        <div id="expandedService" class="mock-wrapper" style="display:none;">

            {{-- HEADER aligned to right column --}}
            <div class="mock-header">
                <img id="expIcon" src="{{ asset('images/services/placeholder-icon.png') }}" alt="Service Icon">

                <h2 id="expTitle">Remote Support</h2>
                <p id="expShort">Instant help wherever you are.</p>

                <button type="button" id="closeExpanded" class="mock-close-btn">
                    Close
                </button>
            </div>

            <hr class="mock-divider">

            <div class="mock-content">
                {{-- IMAGE LEFT --}}
                <div class="mock-image">
                    <img id="expImage"
                         src="{{ asset('images/services/placeholder-image.png') }}"
                         alt="Service Image">
                </div>

                {{-- TEXT RIGHT --}}
                <div class="mock-text">
                    <p id="expLong">
                        Fast, friendly remote support to keep your people working — without waiting days
                        for someone to show up. Screensharing, quick fixes, and real humans on the other end.
                    </p>

                    <h4>Included Services</h4>
                    <ul id="expSubs">
                        {{-- Populated by JS --}}
                    </ul>
                </div>
            </div>

        </div> {{-- /#expandedService --}}

    </section>

</main>
@endsection

@push('scripts')
    <script src="{{ asset('js/services/services.js') }}?v=6969" defer></script>
@endpush
