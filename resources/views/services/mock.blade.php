{{-- resources/views/services/mock.blade.php --}}
@extends('layouts.base')

@section('title', 'SharpLync | Services')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/services/services.css') }}?v=6969">
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

    {{-- Service Tiles --}}
    <div class="hero-cards fade-section services-cards">

        @foreach ($categories as $cat)
        <div class="tile service-tile" data-id="{{ $cat['id'] }}">

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

            {{-- EXPANDED DETAIL --}}
            <div class="tile-detail">

                {{-- DIVIDER LINE --}}
                <hr class="tile-divider">

                @if (!empty($cat['image']))
                <div class="tile-detail-inner">

                    {{-- IMAGE LEFT --}}
                    <div class="detail-image-wrapper">
                        <img src="{{ asset($cat['image']) }}"
                             alt="{{ $cat['title'] }} Image"
                             class="detail-image">
                    </div>

                    {{-- TEXT RIGHT --}}
                    <div class="tile-detail-content">

                        <p>{{ $cat['long'] }}</p>

                        @if (!empty($cat['subs']))
                            <h4>Included Services</h4>
                            <ul>
                                @foreach ($cat['subs'] as $sub)
                                    <li>{{ $sub }}</li>
                                @endforeach
                            </ul>
                        @endif

                    </div>

                </div>

                {{-- NO IMAGE → CLASSIC CENTERED TEXT --}}
                @else
                    <p>{{ $cat['long'] }}</p>
                    @if (!empty($cat['subs']))
                        <h4>Included Services</h4>
                        <ul>
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
