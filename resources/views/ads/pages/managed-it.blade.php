{{-- Google Ads LP: Managed IT Services --}}

@extends('ads.layouts.ads-base')

@section('title', 'SharpLync | Managed IT Services for Small Business')

@section('content')
<div class="ads-hero">
    <div class="ads-eyebrow">Managed IT Services • Proactive Support</div>
    <h1 class="ads-hero-title">
        Ongoing IT care for your business.<br>
        <span class="ads-hero-highlight">Fewer surprises, more uptime.</span>
    </h1>
    <p class="ads-hero-subtitle">
        SharpLync’s managed IT services keep your technology running smoothly with proactive monitoring, 
        security, updates, and friendly support — so you can focus on your business, not your computers.
    </p>

    @include('ads.components.cta-buttons')
    @include('ads.components.trust-badges')
</div>

<section class="ads-section">
    <h2>What’s included in Managed IT?</h2>
    <ul class="ads-list">
        <li>Proactive monitoring of devices and key services</li>
        <li>Security protection using modern, business-grade tools</li>
        <li>Patch management and regular maintenance windows</li>
        <li>Remote support for everyday issues</li>
        <li>Advice on upgrades, replacements and planning</li>
        <li>Optional on-site visits and scheduled reviews</li>
    </ul>
</section>

<section class="ads-section">
    <h2>Why it beats “call when it breaks”</h2>
    <ul class="ads-list">
        <li>Small issues are caught early before they become outages.</li>
        <li>Staff know who to call and how support works.</li>
        <li>You can budget with predictable monthly costs.</li>
        <li>Security tools stay up to date and monitored.</li>
        <li>Your IT slowly becomes tidier, safer and easier to manage.</li>
    </ul>
</section>

<section class="ads-section">
    <h2>Managed IT for small teams (1–20 staff)</h2>
    <p>
        SharpLync focuses on small businesses, regional and local organisations that need 
        the same level of care as larger companies — without the enterprise complexity.
    </p>
    <p>
        We’ll look at your current setup, understand how your team works, and recommend a managed 
        support approach that fits your size, systems and budget.
    </p>
</section>

<section class="ads-section">
    <h2>Book a free, no-pressure IT chat</h2>
    <p>
        Not sure if managed IT is right for you yet? Let’s talk it through, in plain English.
    </p>
    @include('ads.components.cta-buttons')
</section>
@endsection
