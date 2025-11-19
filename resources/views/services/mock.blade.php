{{-- resources/views/services/mock.blade.php --}}
@extends('layouts.base')

@section('title', 'SharpLync | Services')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/services/services.css') }}">
@endpush

@section('content')
<section class="hero services-hero">

    {{-- CPU image --}}
    <div class="hero-cpu-bg">
        <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
    </div>

    {{-- Hero logo --}}
    <img src="{{ asset('images/sharplync-logo.png') }}"
         alt="SharpLync Hero Logo"
         class="hero-logo">

    {{-- Title --}}
    <div class="hero-text">
        <h1>Your Business,<br>
            <span class="highlight">Secure &amp; Connected.</span>
        </h1>
    </div>

    {{-- Tile grid --}}
    <div class="hero-cards fade-section services-cards">
        @foreach ($categories as $cat)
            <div class="tile service-tile {{ !empty($cat['image']) ? 'has-image' : '' }}"
                 data-id="{{ $cat['id'] }}">

                {{-- HEADER — always shown in collapsed mode --}}
                <div class="tile-header">
                    @if (!empty($cat['icon']))
                        <img src="{{ asset($cat['icon']) }}"
                             alt="{{ $cat['title'] }}"
                             class="tile-icon">
                    @endif

                    <h3>{{ $cat['title'] }}</h3>
                    <p>{{ $cat['short'] }}</p>

                    <button type="button" class="tile-toggle">
                        Learn More
                    </button>
                </div>

                {{-- EXPANDED CONTENT — two possible versions --}}
                <div class="tile-detail">

                    {{-- NEW image layout (OPTION B) --}}
                    @if (!empty($cat['image']))
                        <div class="tile-expanded-layout">
                            
                            <div class="tile-media">
                                <img src="{{ asset($cat['image']) }}"
                                     alt="{{ $cat['title'] }} Image">
                            </div>

                            <div class="tile-content">
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
                    @else
                        {{-- ORIGINAL layout (fallback) --}}
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
