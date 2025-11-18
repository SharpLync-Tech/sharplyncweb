{{-- resources/views/services/mock.blade.php --}}
@extends('layouts.base')

@section('title', 'SharpLync | Services')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/services/services.css') }}">
@endpush

@section('content')
<section class="hero services-hero">
    {{-- CPU image, same as home --}}
    <div class="hero-cpu-bg">
        <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
    </div>

    {{-- Center logo, same as home --}}
    <img src="{{ asset('images/sharplync-logo.png') }}"
         alt="SharpLync Hero Logo"
         class="hero-logo">

    {{-- Hero text, services-specific --}}
    <div class="hero-text">
        <h1>Your Business,<br>
            <span class="highlight">Secure &amp; Connected.</span>
        </h1>
        <p>
            From the Granite Belt to the Cloud — managed IT, cloud, and security
            that keeps your systems running sharp, safe, and simple.
        </p>
    </div>

    {{-- Service tiles: same style as home (.tile), but with extra behaviour --}}
    <div class="hero-cards fade-section services-cards">
        @foreach ($categories as $cat)
            <div class="tile service-tile" data-id="{{ $cat['id'] }}">
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
            </div>
        @endforeach
    </div>
</section>
@endsection

@push('scripts')
    <script src="{{ asset('js/services/services.js') }}" defer></script>
@endpush
