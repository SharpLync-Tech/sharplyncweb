{{-- Google Ads LP: IT Support Stanthorpe --}}

@extends('ads.layouts.ads-base')

@section('title', 'SharpLync | IT Support Stanthorpe & Granite Belt')

@section('content')
<div class="ads-hero">
    <div class="ads-eyebrow">Local IT Support • Stanthorpe Region</div>
    <h1 class="ads-hero-title">
        Local IT support for Stanthorpe businesses.<br>
        <span class="ads-hero-highlight">From the Granite Belt to the cloud.</span>
    </h1>
    <p class="ads-hero-subtitle">
        SharpLync provides local, security-first IT support for businesses in Stanthorpe and the wider Granite Belt — 
        combining modern cloud tools with down-to-earth service.
    </p>

    @include('ads.components.cta-buttons')
    @include('ads.components.trust-badges')
</div>

<section class="ads-section">
    <h2>Who we help in Stanthorpe</h2>
    <ul class="ads-list">
        <li>Small businesses with a few computers and staff</li>
        <li>Local trades and services needing reliable email and scheduling</li>
        <li>Hospitality, retail and tourism operators</li>
        <li>Professional services and home-based businesses</li>
    </ul>
</section>

<section class="ads-section">
    <h2>Services available in your area</h2>
    <ul class="ads-list">
        <li>Remote IT support for everyday problems</li>
        <li>On-site visits in and around Stanthorpe (by arrangement)</li>
        <li>Cybersecurity and scam awareness support</li>
        <li>Microsoft 365, email and cloud assistance</li>
        <li>Network, Wi-Fi and backup guidance</li>
    </ul>
</section>

<section class="ads-section">
    <h2>Talk to a local about your IT</h2>
    <p>Based in the Granite Belt and focused on regional businesses, SharpLync understands how you work.</p>
    @include('ads.components.cta-buttons')
</section>
@endsection
