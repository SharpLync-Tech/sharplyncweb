{{-- Google Ads LP: SafeCheck / Scam & Fraud Prevention --}}

@extends('ads.layouts.ads-base')

@section('title', 'SharpLync | Scam Check & Email Safety (SafeCheck)')

@section('content')
<div class="ads-hero">
    <div class="ads-eyebrow">Scam Checks • Email Safety • Small Business Protection</div>
    <h1 class="ads-hero-title">
        Not sure if that email or call is real?<br>
        <span class="ads-hero-highlight">Ask SharpLync before you click.</span>
    </h1>
    <p class="ads-hero-subtitle">
        SharpLync SafeCheck helps you spot scams, dodgy emails and suspicious requests before they cost your business 
        money, data or access. Get a professional opinion instead of guessing.
    </p>

    @include('ads.components.cta-buttons')
    @include('ads.components.trust-badges')

    <p class="ads-hero-note">
        Many scams look incredibly convincing. It’s always safer to check first than to try recover after the fact.
    </p>
</div>

<section class="ads-section">
    <h2>How SafeCheck works</h2>
    <ul class="ads-list">
        <li>You forward or share the suspicious email/message details with us.</li>
        <li>We review the links, sender, wording and technical details safely.</li>
        <li>You get a clear answer: likely scam, safe, or proceed with caution.</li>
        <li>We can help improve staff awareness at the same time.</li>
    </ul>
</section>

<section class="ads-section">
    <h2>Why businesses love SafeCheck</h2>
    <ul class="ads-list">
        <li>Staff have “someone to ask” instead of guessing.</li>
        <li>Reduces the risk of one wrong click costing thousands.</li>
        <li>Builds a more cautious, security-aware culture.</li>
        <li>Supports your existing security tools and policies.</li>
    </ul>
</section>

<section class="ads-section">
    <h2>Get scam-aware support for your team</h2>
    <p>Give your staff permission to double-check anything that doesn’t feel right — with SharpLync in their corner.</p>
    @include('ads.components.cta-buttons')
</section>
@endsection
