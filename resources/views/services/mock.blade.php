{{-- resources/views/services/mock.blade.php --}}
@extends('layouts.base')

@section('title', 'SharpLync | Services')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/services/services.css') }}?v=6969">
@endpush

@section('content')
<section class="hero services-hero">

    {{-- CPU BG --}}
    <div class="hero-cpu-bg">
        <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
    </div>

    {{-- Logo --}}
    <img src="{{ asset('images/sharplync-logo.png') }}"
         class="hero-logo"
         alt="SharpLync Hero Logo">

    {{-- MAIN HEADING --}}
    <div class="hero-text">
        <h1>Your Business,<br><span class="highlight">Secure & Connected.</span></h1>
    </div>

    {{-- SERVICE CARDS --}}
    <div class="hero-cards fade-section services-cards">

        @foreach ($categories as $cat)
        <div class="tile service-tile" data-id="{{ $cat['id'] }}">

            {{-- HEADER (REUSED FOR OPEN & CLOSED STATE) --}}
            <div class="tile-header">
                @if (!empty($cat['icon']))
                    <img src="{{ asset($cat['icon']) }}"
                         class="tile-icon"
                         alt="{{ $cat['title'] }} Icon">
                @endif

                <h3>{{ $cat['title'] }}</h3>
                <p>{{ $cat['short'] }}</p>

                <button type="button" class="tile-toggle">Learn More</button>
            </div>

            {{-- DETAIL (Open State) --}}
            <div class="tile-detail">

                {{-- Top Divider (aligned with text column) --}}
                <hr class="tile-divider">

                @if (!empty($cat['image']))
                <div class="tile-detail-inner">

                    {{-- IMAGE LEFT --}}
                    <div class="detail-image-wrapper">
                        <img src="{{ asset($cat['image']) }}"
                             class="detail-image"
                             alt="{{ $cat['title'] }} Image">
                    </div>

                    {{-- TEXT RIGHT --}}
                    <div class="tile-detail-content">
                        <p>{{ $cat['long'] }}</p>

                        @if (!empty($cat['subs']))
                            <h4>Included Services</h4>
                            <ul class="arrow-list">
                                @foreach ($cat['subs'] as $sub)
                                    <li>{{ $sub }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                </div>
                @else
                    {{-- NO IMAGE - CENTERED TEXT --}}
                    <p>{{ $cat['long'] }}</p>

                    @if (!empty($cat['subs']))
                        <h4>Included Services</h4>
                        <ul class="arrow-list">
                            @foreach ($cat['subs'] as $sub)
                                <li>{{ $sub }}</li>
                            @endforeach
                        </ul>
                    @endif
                @endif

            </div>

        </div>
        @endforeach

    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/services/services.js') }}" defer></script>
@endpush
