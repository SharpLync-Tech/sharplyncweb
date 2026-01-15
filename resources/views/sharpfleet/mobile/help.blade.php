@extends('sharpfleet.mobile.layouts.app')

@section('title', 'Help')

@section('content')
<section class="sf-mobile-dashboard">
    <h1 class="sf-mobile-title">Quick Driver Help</h1>
    <p class="sf-mobile-subtitle">Simple steps for starting and ending trips.</p>

    <div class="sf-mobile-card">
        <div class="sf-mobile-card-title">Starting a trip</div>
        <div class="sf-mobile-card-text">
            <div>Open the app and tap Start.</div>
            <div>Select the vehicle you’re using.</div>
            <div>Enter anything required (for example: start reading, trip type, customer).</div>
            <div>Tap Start Trip.</div>
            <div>If something is required, SharpFleet will tell you before you can continue.</div>
        </div>
    </div>

    <div class="sf-mobile-card" style="margin-top: 12px;">
        <div class="sf-mobile-card-title">Ending a trip</div>
        <div class="sf-mobile-card-text">
            <div>Open the app.</div>
            <div>Find the active trip on the Home screen.</div>
            <div>Enter any required end details (end time or reading).</div>
            <div>Tap End Trip.</div>
            <div>End the trip as soon as practical to keep records accurate.</div>
        </div>
    </div>

    <div class="sf-mobile-card" style="margin-top: 12px;">
        <div class="sf-mobile-card-title">If you can’t start or end a trip</div>
        <div class="sf-mobile-card-text">
            <div>A required field is missing — scroll up and check the message.</div>
            <div>If you’re offline, the trip may sync later when you’re back online.</div>
            <div>If something doesn’t look right, tell your admin instead of guessing.</div>
        </div>
    </div>

    <div class="sf-mobile-card" style="margin-top: 12px;">
        <div class="sf-mobile-card-title">Good to know</div>
        <div class="sf-mobile-card-text">
            <div>No GPS is required.</div>
            <div>Your company decides what details are needed.</div>
            <div>SharpFleet won’t track you without input.</div>
        </div>
    </div>
</section>
@endsection
