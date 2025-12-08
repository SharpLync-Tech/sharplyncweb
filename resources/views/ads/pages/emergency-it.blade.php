{{-- Google Ads LP: Emergency IT Support --}}

@extends('ads.layouts.ads-base')

@section('title', 'SharpLync | Emergency IT Support')

@section('content')
<div class="ads-hero">
    <div class="ads-eyebrow">Urgent IT Help • Same-Day Where Possible</div>
    <h1 class="ads-hero-title">
        Something’s broken and you need it fixed <span class="ads-hero-highlight">now</span>.
    </h1>
    <p class="ads-hero-subtitle">
        If your systems are down, email has stopped, or a key computer has died, SharpLync 
        provides urgent remote and local IT help so your business can get moving again quickly.
    </p>

    @include('ads.components.cta-buttons')
    @include('ads.components.trust-badges')

    <p class="ads-hero-note">
        Same-day assistance is offered where possible. We’ll always be upfront about what we can do and how quickly.
    </p>
</div>

<section class="ads-section">
    <h2>We prioritise real business impact</h2>
    <ul class="ads-list">
        <li>Critical systems not working (POS, key line-of-business apps)</li>
        <li>Business-wide internet or network outage</li>
        <li>Key staff unable to work due to device failure</li>
        <li>Suspected security incident or scam interaction</li>
        <li>Locked out of essential accounts or services</li>
    </ul>
</section>

<section class="ads-section">
    <h2>What to expect when you call</h2>
    <ul class="ads-list">
        <li>We’ll quickly assess the situation and impact.</li>
        <li>If we can help remotely, we’ll start there immediately.</li>
        <li>If on-site is required, we’ll discuss timeframes and travel.</li>
        <li>You’ll get clear communication while we diagnose and fix.</li>
        <li>We’ll advise on next steps to prevent repeat issues.</li>
    </ul>
</section>

<section class="ads-section">
    <h2>Need urgent help?</h2>
    <p>Call now and tell us what’s happening. We’ll be honest about what we can do and how fast.</p>
    @include('ads.components.cta-buttons')
</section>
@endsection
