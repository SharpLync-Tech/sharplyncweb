{{-- Google Ads LP: Cybersecurity / Trend Micro --}}

@extends('ads.layouts.ads-base')

@section('title', 'SharpLync | Small Business Cybersecurity & Protection')

@section('content')
<div class="ads-hero">
    <div class="ads-eyebrow">Cybersecurity • Trend Micro Partner</div>
    <h1 class="ads-hero-title">
        Cybersecurity that actually makes sense.<br>
        <span class="ads-hero-highlight">Serious protection, simple to run.</span>
    </h1>
    <p class="ads-hero-subtitle">
        SharpLync brings enterprise-grade security to everyday businesses using Trend Micro protection, 
        secure remote support and practical policies that staff can actually follow.
    </p>

    @include('ads.components.cta-buttons')
    @include('ads.components.trust-badges')

    <p class="ads-hero-note">
        From scam emails and ransomware to unsafe remote access, we help you reduce real-world risk 
        without slowing your business to a crawl.
    </p>
</div>

<section class="ads-section">
    <h2>Security services we provide</h2>
    <ul class="ads-list">
        <li>Business-grade endpoint protection and monitoring</li>
        <li>Email and phishing protection, including scam awareness</li>
        <li>Multi-factor authentication and secure sign-in support</li>
        <li>Secure remote support processes and tools</li>
        <li>Backup and recovery advice for critical data</li>
        <li>Simple security policies staff can understand and follow</li>
    </ul>
</section>

<section class="ads-section">
    <h2>Why security is non-negotiable now</h2>
    <p>
        Small businesses are a prime target for scams and cyberattacks because they’re busy, 
        trusting and often under-protected. One bad click can mean lost data, locked systems, 
        or money transferred to a scammer.
    </p>
    <p>
        We combine technology, process and education so your business isn’t an easy target.
    </p>
</section>

<section class="ads-section">
    <h2>Security that fits your size and budget</h2>
    <p>
        We’ll start with where you are today, look at your risks and priorities, and build 
        a practical plan, not a giant security project you don’t need.
    </p>
    @include('ads.components.cta-buttons')
</section>
@endsection
