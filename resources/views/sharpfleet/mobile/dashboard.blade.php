@extends('sharpfleet.mobile.layouts.app')

@section('title', 'Driver')

@section('content')
<section class="sf-mobile-dashboard">

    <h1 class="sf-mobile-title">Ready to Drive</h1>

    <p class="sf-mobile-subtitle">
        No active trip
    </p>

    <button class="sf-mobile-primary-btn">
        Start Drive
    </button>

    <button class="sf-mobile-secondary-btn">
        Report Vehicle Issue
    </button>

    {{-- PWA install button (Chrome / Android only) --}}
    <button
        id="pwa-install-btn"
        onclick="installPWA()"
        class="sf-mobile-secondary-btn"
        style="display:none"
    >
        📲 Install SharpFleet App
    </button>

    {{-- iOS install hint --}}
    <div
        id="ios-install-hint"
        class="sf-mobile-secondary-btn"
        style="display:none"
    >
        👉 Tap <strong>Share</strong> → <strong>Add to Home Screen</strong>
    </div>

</section>
@endsection
