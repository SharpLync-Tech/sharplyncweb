{{-- Google Ads LP: IT Consultation / Assessment --}}

@extends('ads.layouts.ads-base')

@section('title', 'SharpLync | Business IT Assessment & Consultation')

@section('content')
<div class="ads-hero">
    <div class="ads-eyebrow">IT Assessment • Business Consultation</div>
    <h1 class="ads-hero-title">
        Not sure where to start with your IT?<br>
        <span class="ads-hero-highlight">Let’s talk it through properly.</span>
    </h1>
    <p class="ads-hero-subtitle">
        SharpLync offers practical IT assessments for small businesses — looking at security, reliability, backups and 
        day-to-day pain points — with plain-English recommendations and options.
    </p>

    @include('ads.components.cta-buttons')
    @include('ads.components.trust-badges')
</div>

<section class="ads-section">
    <h2>What we look at</h2>
    <ul class="ads-list">
        <li>Your current devices and how staff use them</li>
        <li>Internet, Wi-Fi and network stability</li>
        <li>Backup and recovery preparedness</li>
        <li>Security tools, policies and backup habits</li>
        <li>Email, Microsoft 365 and cloud usage</li>
        <li>Any industry-specific software you rely on</li>
    </ul>
</section>

<section class="ads-section">
    <h2>What you get</h2>
    <ul class="ads-list">
        <li>Plain-English summary of where you’re at today</li>
        <li>Key risks and quick wins identified</li>
        <li>Suggested options based on your size and budget</li>
        <li>No pressure to commit to anything on the spot</li>
    </ul>
</section>

<section class="ads-section">
    <h2>Book a conversation with SharpLync</h2>
    <p>Let’s take 30–45 minutes to understand your business and where your IT can better support it.</p>
    @include('ads.components.cta-buttons')
</section>
@endsection
