{{-- Google Ads LP: IT Support Warwick --}}

@extends('ads.layouts.ads-base')

@section('title', 'SharpLync | IT Support Warwick QLD')

@section('content')
<div class="ads-hero">
    <div class="ads-eyebrow">Business IT Support • Warwick</div>
    <h1 class="ads-hero-title">
        Practical IT support for Warwick businesses.<br>
        <span class="ads-hero-highlight">Modern tools, no nonsense.</span>
    </h1>
    <p class="ads-hero-subtitle">
        SharpLync supports small businesses in Warwick with straightforward IT help, cybersecurity, Microsoft 365 
        and remote support that prioritises real-world outcomes.
    </p>

    @include('ads.components.cta-buttons')
    @include('ads.components.trust-badges')
</div>

<section class="ads-section">
    <h2>Support for Warwick businesses</h2>
    <ul class="ads-list">
        <li>Remote and selective on-site support</li>
        <li>Help with slow, unstable or outdated systems</li>
        <li>Cybersecurity and scam-awareness support</li>
        <li>Backup, recovery and continuity guidance</li>
        <li>New device setup and ongoing care</li>
    </ul>
</section>

<section class="ads-section">
    <h2>Regional friendly, security focused</h2>
    <p>
        We understand the realities of regional business — mixed devices, older systems, 
        and limited time. Our goal is to tidy things up, secure them and make them easier to live with.
    </p>
    @include('ads.components.cta-buttons')
</section>
@endsection
