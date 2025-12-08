{{-- Google Ads LP: New Computer Setup & Hardware --}}

@extends('ads.layouts.ads-base')

@section('title', 'SharpLync | New Computer Setup & Hardware Support')

@section('content')
<div class="ads-hero">
    <div class="ads-eyebrow">PC & Laptop Setup • Business Hardware</div>
    <h1 class="ads-hero-title">
        New computers without the chaos.<br>
        <span class="ads-hero-highlight">Set up properly from day one.</span>
    </h1>
    <p class="ads-hero-subtitle">
        SharpLync helps you choose, set up and configure business computers and devices so your staff 
        can log in and get to work without all the usual teething issues.
    </p>

    @include('ads.components.cta-buttons')
    @include('ads.components.trust-badges')
</div>

<section class="ads-section">
    <h2>What we handle for you</h2>
    <ul class="ads-list">
        <li>Initial Windows or device setup and updates</li>
        <li>Joining to your Microsoft 365 / business environment</li>
        <li>Installing required business applications</li>
        <li>Setting up printers, scanners and shared drives</li>
        <li>Applying basic security and backup settings</li>
        <li>Retiring or wiping old devices safely</li>
    </ul>
</section>

<section class="ads-section">
    <h2>Why it matters</h2>
    <p>
        A properly set up computer is faster, safer and more reliable. Staff lose less time, 
        and you avoid hidden issues that show up months later.
    </p>
</section>

<section class="ads-section">
    <h2>Plan your next round of upgrades</h2>
    <p>Talk to SharpLync about what you have now and what’s coming up for renewal or replacement.</p>
    @include('ads.components.cta-buttons')
</section>
@endsection
