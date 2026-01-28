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

    <a href="/app/sharpfleet/mobile/support" class="sf-mobile-list-item">
        <span>Support</span>
    </a>

    <a href="/app/sharpfleet/mobile/about" class="sf-mobile-list-item">
        <span>About SharpFleet</span>
    </a>

    <a href="/app/sharpfleet/logout" class="sf-mobile-list-item">
        <span>Log out</span>
    </a>

</section>
@endsection
