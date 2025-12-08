{{-- Google Ads LP: Remote Support Download / Session --}}

@extends('ads.layouts.ads-base')

@section('title', 'SharpLync | Secure Remote IT Support')

@section('content')
<div class="ads-hero">
    <div class="ads-eyebrow">Remote IT Support • Secure Access</div>
    <h1 class="ads-hero-title">
        Get help without waiting for a site visit.<br>
        <span class="ads-hero-highlight">Secure remote support from SharpLync.</span>
    </h1>
    <p class="ads-hero-subtitle">
        We use secure, verified remote tools so a SharpLync technician can connect to your computer, 
        diagnose the problem and help fix it — with your permission and full visibility.
    </p>

    @include('ads.components.cta-buttons')
    @include('ads.components.trust-badges')
</div>

<section class="ads-section">
    <h2>How remote support works</h2>
    <ul class="ads-list">
        <li>You call us and explain the issue in simple terms.</li>
        <li>We provide a link to download our remote support tool.</li>
        <li>You read and accept the safety information.</li>
        <li>We connect using a one-time code or session ID.</li>
        <li>You can see everything happening on your screen.</li>
    </ul>
</section>

<section class="ads-section">
    <h2>Your security is the priority</h2>
    <p>
        SharpLync follows strict verification and security procedures for all remote sessions. 
        We make it very hard for scammers to pretend to be us.
    </p>
    <p>
        If something doesn’t seem right, we’ll show you how to quickly end a session or disconnect.
    </p>
</section>

<section class="ads-section">
    <h2>Need remote support?</h2>
    <p>Contact us first so we can confirm details, then we’ll guide you step-by-step.</p>
    @include('ads.components.cta-buttons')
</section>
@endsection
