{{-- Google Ads LP: IT Support Granite Belt --}}

@extends('ads.layouts.ads-base')

@section('title', 'SharpLync | IT Support Granite Belt Region')

@section('content')
<div class="ads-hero">
    <div class="ads-eyebrow">Granite Belt • Regional Business IT</div>
    <h1 class="ads-hero-title">
        IT support for the Granite Belt.<br>
        <span class="ads-hero-highlight">Local heart, modern tech.</span>
    </h1>
    <p class="ads-hero-subtitle">
        From small offices and farms to tourism and retail, SharpLync supports Granite Belt businesses with 
        straightforward IT, cybersecurity and remote support.
    </p>

    @include('ads.components.cta-buttons')
    @include('ads.components.trust-badges')
</div>

<section class="ads-section">
    <h2>Supporting regional businesses</h2>
    <ul class="ads-list">
        <li>Small business IT support and troubleshooting</li>
        <li>Cybersecurity and scam protection guidance</li>
        <li>Cloud and Microsoft 365 assistance</li>
        <li>Backup and recovery planning</li>
        <li>Network and Wi-Fi improvements</li>
    </ul>
</section>

<section class="ads-section">
    <h2>From the bush to the cloud</h2>
    <p>
        We understand regional realities: patchy internet, older hardware and mixed systems. 
        Our focus is making what you have work better, then improving step by step.
    </p>
    @include('ads.components.cta-buttons')
</section>
@endsection
