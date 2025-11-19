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


    {{-- EXPANDED CLEAN MOCK --}}

<div class="mock-wrapper">

    {{-- FULL INLINE STYLES SO NOTHING CAN INTERFERE --}}
    <style>
        body .mock-wrapper {
            max-width: 1200px;
            margin: 4rem auto;
            background: #0A2A4D !important;
            border-radius: 18px;
            padding: 3rem;
            box-shadow: 0 12px 32px rgba(0,0,0,0.35);
            color: white !important;
            position: relative;
            z-index: 9999;
        }

        /* HEADER */
        .mock-header {
            text-align: center;
            width: 100%;
            max-width: 600px;
            margin-left: calc(420px + 3rem);
        }

        .mock-header img {
            width: 70px;
            filter:
                drop-shadow(0 0 4px rgba(44,191,174,1))
                drop-shadow(0 0 16px rgba(44,191,174,0.7));
            margin-bottom: 1rem;
        }

        .mock-header h2 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 600;
        }

        .mock-header p {
            margin-top: .3rem;
            opacity: 0.85;
            font-size: 0.95rem;
        }

        /* CLOSE BUTTON */
        .mock-close-btn {
            margin-top: 1rem;
            padding: 0.55rem 1.6rem;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(44,191,174,0.65);
            border-radius: 999px;
            font-size: 0.95rem;
            color: white;
            cursor: pointer;
            transition: all 0.25s ease;
            backdrop-filter: blur(4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.35);
        }

        .mock-close-btn:hover {
            background: rgba(44,191,174,0.25);
            box-shadow: 0 0 15px rgba(44,191,174,0.8);
            transform: translateY(-2px);
        }

        /* DIVIDER */
        .mock-divider {
            width: 600px;
            margin-left: calc(420px + 3rem);
            border: none;
            border-top: 1px solid rgba(255,255,255,0.18);
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        /* GRID */
        .mock-content {
            display: grid;
            grid-template-columns: 420px 1fr;
            gap: 3rem;
            align-items: flex-start;
        }

        .mock-image img {
            width: 100%;
            border-radius: 14px;
            object-fit: cover;
            box-shadow: 0 8px 25px rgba(0,0,0,0.35);
            margin-top: -13rem;
        }

        .mock-text {
            max-width: 650px;
        }

        .mock-text p {
            margin-bottom: 1.5rem;
            line-height: 1.55;
            opacity: 0.95;
        }

        .mock-text h4 {
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            font-weight: 600;
            text-align: center;
        }

        /* ARROW BULLETS */
        .mock-text ul {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        .mock-text li {
            margin-bottom: .6rem;
            position: relative;
            padding-left: 1.4rem;
        }

        .mock-text li::before {
            content: "➜";
            position: absolute;
            left: 0;
            top: 0;
            color: #2CBFAE;
            font-weight: 700;
        }

        /* MOBILE */
        @media (max-width: 768px) {
            .mock-wrapper {
                padding: 2rem;
            }
            .mock-header,
            .mock-divider {
                margin-left: 0;
                width: 100%;
                max-width: 100%;
            }
            .mock-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .mock-image img {
                margin-top: 0;
            }
            .mock-text ul {
                text-align: left;
                display: inline-block;
            }
        }
    </style>

    {{-- HEADER --}}
    <div class="mock-header">
        <img src="{{ asset($cat['icon']) }}" alt="">
        <h2>{{ $cat['title'] }}</h2>
        <p>{{ $cat['short'] }}</p>

        {{-- CLOSE BTN --}}
        <button class="mock-close-btn" onclick="window.location.href='{{ route('services.mock') }}'">
            Close
        </button>
    </div>

    <hr class="mock-divider">

    <div class="mock-content">
        <div class="mock-image">
            <img src="{{ asset($cat['image']) }}" alt="">
        </div>

        <div class="mock-text">
            <p>{{ $cat['long'] }}</p>

            <h4>Included Services</h4>
            <ul>
                @foreach ($cat['subs'] as $s)
                <li>{{ $s }}</li>
                @endforeach
            </ul>
        </div>
    </div>

</div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/services/services.js') }}?v=9001" defer></script>
@endpush
