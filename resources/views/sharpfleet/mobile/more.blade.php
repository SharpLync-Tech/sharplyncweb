@extends('sharpfleet.mobile.layouts.app')

@section('title', 'More')

@section('content')
<section class="sf-mobile-dashboard">

    <h1 class="sf-mobile-title">More</h1>

    <p class="sf-mobile-subtitle">
        Settings and extras.
    </p>

    {{-- Install block --}}
    <div class="sf-mobile-card">
        <h2 class="sf-mobile-card-title">Install SharpFleet</h2>
        <p class="sf-mobile-card-text">
            Installing makes it faster to open and feels more like a real app.
        </p>

        {{-- Chrome / Edge / Android install --}}
        <button
            id="pwa-install-btn"
            onclick="installPWA()"
            class="sf-mobile-secondary-btn"
            style="display:none"
            type="button"
        >
            📲 Install SharpFleet App
        </button>

        {{-- iOS hint (Safari / iPhone) --}}
        <div
            id="ios-install-hint"
            class="sf-mobile-install-hint"
            style="display:none"
        >
            <div class="sf-mobile-install-hint-title">Install on iPhone</div>
            <div class="sf-mobile-install-hint-text">
                Tap <strong>Share</strong> then <strong>Add to Home Screen</strong>.
            </div>
        </div>
    </div>

    {{-- Placeholder links (wire later) --}}
    <a class="sf-mobile-list-item" href="/app/sharpfleet/driver/help">
        <span>Help</span>
        <span class="sf-mobile-list-item-arrow">›</span>
    </a>

    <a class="sf-mobile-list-item" href="/app/sharpfleet/driver/about">
        <span>About</span>
        <span class="sf-mobile-list-item-arrow">›</span>
    </a>

    <a class="sf-mobile-list-item" href="/app/sharpfleet/logout">
        <span>Logout</span>
        <span class="sf-mobile-list-item-arrow">›</span>
    </a>

</section>
@endsection
