@extends('sharpfleet.mobile.layouts.app')

@section('title', 'More')

@section('content')
<section class="sf-mobile-dashboard">

    <h1 class="sf-mobile-title">More</h1>
    <p class="sf-mobile-subtitle">Settings and extras.</p>

    {{-- Links --}}
    <a href="/app/sharpfleet/mobile/bookings" class="sf-mobile-list-item">
        <span>Bookings</span>
    </a>

    <a href="/app/sharpfleet/mobile/help" class="sf-mobile-list-item">
        <span>Help</span>
    </a>

    <a href="/app/sharpfleet/mobile/about" class="sf-mobile-list-item">
        <span>About SharpFleet</span>
    </a>

    {{-- Install App --}}
    <div class="sf-mobile-card">
        <h3 class="sf-mobile-card-title">Install SharpFleet</h3>

        <p class="sf-mobile-card-text">
            Installing makes SharpFleet faster to open and feel more like a real app.
        </p>

        <div class="sf-mobile-install-hint">
            <div class="sf-mobile-install-hint-title">iPhone</div>
            <div class="sf-mobile-install-hint-text">
                Tap <strong>Share</strong> then <strong>Add to Home Screen</strong>.
            </div>
        </div>
    </div>

    <a href="/app/sharpfleet/logout" class="sf-mobile-list-item">
        <span>Log out</span>
    </a>

</section>
@endsection
