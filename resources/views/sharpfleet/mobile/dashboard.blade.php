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

        <button
            id="pwa-install-btn"
            onclick="installPWA()"
            style="display:none"
            class="sf-mobile-secondary-btn"
        >
            📲 Install SharpFleet App
        </button>


    </section>
@endsection
