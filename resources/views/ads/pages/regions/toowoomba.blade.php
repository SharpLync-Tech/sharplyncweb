{{-- Google Ads LP: IT Support Toowoomba --}}

@extends('ads.layouts.ads-base')

@section('title', 'SharpLync | IT Support Toowoomba')

@section('content')
<div class="ads-hero">
    <div class="ads-eyebrow">Business IT Support • Toowoomba</div>
    <h1 class="ads-hero-title">
        IT support for Toowoomba’s small businesses.<br>
        <span class="ads-hero-highlight">Secure, simple and reliable.</span>
    </h1>
    <p class="ads-hero-subtitle">
        SharpLync helps Toowoomba businesses with practical IT support, cybersecurity, Microsoft 365 and remote 
        assistance — focused on keeping things running and secure.
    </p>

    @include('ads.components.cta-buttons')
    @include('ads.components.trust-badges')
</div>

<section class="ads-section">
    <h2>Services we offer Toowoomba clients</h2>
    <ul class="ads-list">
        <li>Remote troubleshooting and support</li>
        <li>Microsoft 365 setup and optimisation</li>
        <li>Security and backup improvements</li>
        <li>Network and Wi-Fi stability work</li>
        <li>Advice on hardware refresh and planning</li>
    </ul>
</section>

<section class="ads-section">
    <h2>IT support without the big-city attitude</h2>
    <p>Clear communication, realistic recommendations, and support that respects your time and budget.</p>
    @include('ads.components.cta-buttons')
</section>
@endsection
