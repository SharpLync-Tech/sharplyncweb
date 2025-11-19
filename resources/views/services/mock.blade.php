{{-- resources/views/services/mock.blade.php --}}
@extends('layouts.base')

@section('title', 'SharpLync | Services')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/services/services.css') }}">
@endpush

@section('content')
<section class="hero services-hero">

    {{-- CPU Background --}}
    <div class="hero-cpu-bg">
        <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
    </div>

    {{-- Hero Logo --}}
    <img src="{{ asset('images/sharplync-logo.png') }}"
         alt="SharpLync Logo"
         class="hero-logo">

    {{-- Hero Text --}}
    <div class="hero-text">
        <h1>Your Business,<br>
            <span class="highlight">Secure &amp; Connected.</span>
        </h1>
    </div>

    {{-- SERVICE TILES --}}
    <div class="hero-cards fade-section services-cards">

        @foreach ($categories as $cat)
            <div class="tile service-tile" data-id="{{ $cat['id'] }}">

                {{-- NEW EXPANDED LAYOUT WRAPPER --}}
                <div class="tile-expanded-layout">

                    {{-- LEFT SIDE IMAGE --}}
                    <div class="tile-media">
                        @if (!empty($cat['image']))
                            <img src="{{ asset($cat['image']) }}" alt="{{ $cat['title'] }}">
                        @else
                            {{-- Optional fallback --}}
                            <img src="{{ asset('images/default-service.jpg') }}" alt="{{ $cat['title'] }}">
                        @endif
                    </div>

                    {{-- RIGHT CONTENT BLOCK --}}
                    <div class="tile-content">

                        {{-- HEADER / ICON / SHORT TEXT --}}
                        <div class="tile-header">
                            @if (!empty($cat['icon']))
                                <img src="{{ asset($cat['icon']) }}"
                                     alt="{{ $cat['title'] }}"
                                     class="tile-icon">
                            @endif

                            <h3>{{ $cat['title'] }}</h3>
                            <p class="tile-short">{{ $cat['short'] }}</p>
                        </div>

                        {{-- BUTTON --}}
                        <button type="button" class="tile-toggle">
                            Learn More
                        </button>

                        {{-- DETAILS / DESCRIPTION --}}
                        <div class="tile-detail">
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

                    </div>{{-- end tile-content --}}
                </div>{{-- end tile-expanded-layout --}}

            </div>
        @endforeach

    </div>

</section>
@endsection

@push('scripts')
    <script src="{{ asset('js/services/services.js') }}" defer></script>
@endpush
