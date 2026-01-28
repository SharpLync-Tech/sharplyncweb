@extends('sharpfleet.mobile.layouts.app')

@section('title', 'About')

@section('content')
<section class="sf-mobile-dashboard">
    <h1 class="sf-mobile-title">About SharpFleet</h1>
    <p class="sf-mobile-subtitle">Fleet logbook and trip tracking for real-world work.</p>

    <div class="sf-mobile-card">
        <div class="sf-mobile-card-title">SharpFleet</div>
        <div class="sf-mobile-card-text">
            SharpFleet is a fleet logbook and trip tracking app designed for real-world work.
        </div>
        <div class="sf-mobile-card-text">
            It is a product of SharpLync Pty Ltd, an Australian technology company focused on practical, reliable systems for businesses.
        </div>
    </div>

    <div class="sf-mobile-card" style="margin-top: 12px;">
        <div class="sf-mobile-card-title">Version</div>
        <div class="sf-mobile-card-text">v1.2.1 (Mobile)</div>
    </div>

    <div class="sf-mobile-card" style="margin-top: 12px;">
        <div class="sf-mobile-card-title">Feedback</div>
        <div class="sf-mobile-card-text">
            Have a suggestion or spotted something that could be better?
        </div>
        <div class="sf-mobile-card-text">
            We'd love to hear from you.
        </div>
        <div class="sf-mobile-card-text">
            info@sharplync.com.au
        </div>
    </div>

    <div class="sf-mobile-card" style="margin-top: 12px;">
        <div class="sf-mobile-card-title">Good to know</div>
        <div class="sf-mobile-card-text">No GPS. No hardware. No micromanagement.</div>
    </div>

    <div class="sf-mobile-card" style="margin-top: 12px;">
        <div class="sf-mobile-card-title">Terms and Conditions</div>
        <div class="sf-mobile-card-text">
            Available on the SharpFleet website at sharplync.com.au.
        </div>
    </div>

    <div class="sf-mobile-card" style="margin-top: 12px;">
        <div class="sf-mobile-card-text">
            © 2026 SharpLync Pty Ltd. All rights reserved.
        </div>
    </div>
</section>
@endsection
