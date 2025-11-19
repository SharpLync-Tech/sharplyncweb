{{-- resources/views/services/mock.blade.php --}}
@extends('layouts.base')

@section('title', 'SharpLync | Services')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/services/services.css') }}?v=6970">
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

            {{-- EXPANDED CARD --}}
            <div class="tile-detail">

                <div class="expanded-wrapper">

                    {{-- HEADER ABOVE TEXT COLUMN --}}
                    <div class="expanded-header">
                        @if (!empty($cat['icon']))
                        <img src="{{ asset($cat['icon']) }}" class="expanded-icon" alt="">
                        @endif
                        <h2>{{ $cat['title'] }}</h2>
                        <p>{{ $cat['short'] }}</p>

                        <button type="button" class="expanded-close">
                            Close
                        </button>
                    </div>

                    {{-- DIVIDER --}}
                    <hr class="expanded-divider">

                    {{-- CONTENT ROW --}}
                    <div class="expanded-content">

                        {{-- IMAGE LEFT --}}
                        @if (!empty($cat['image']))
                        <div class="expanded-image">
                            <img src="{{ asset($cat['image']) }}" alt="{{ $cat['title'] }} Image">
                        </div>
                        @endif

                        {{-- TEXT RIGHT --}}
                        <div class="expanded-text">
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

                </div>

            </div>

        </div>
        @endforeach

    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/services/services.js') }}" defer></script>
@endpush
