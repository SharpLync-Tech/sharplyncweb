{{-- Google Ads LP: Microsoft 365 Support --}}

@extends('ads.layouts.ads-base')

@section('title', 'SharpLync | Microsoft 365 Support for Business')

@section('content')
<div class="ads-hero">
    <div class="ads-eyebrow">Microsoft 365 • Email • Teams • OneDrive</div>
    <h1 class="ads-hero-title">
        Make Microsoft 365 work for your business,<br>
        <span class="ads-hero-highlight">not against it.</span>
    </h1>
    <p class="ads-hero-subtitle">
        SharpLync helps you set up, fix and properly manage Microsoft 365 — including email, 
        Teams, OneDrive and SharePoint — so your staff can work reliably and securely from anywhere.
    </p>

    @include('ads.components.cta-buttons')
    @include('ads.components.trust-badges')
</div>

<section class="ads-section">
    <h2>What we help with</h2>
    <ul class="ads-list">
        <li>New Microsoft 365 setups for small businesses</li>
        <li>Moving from personal email (Gmail, Hotmail, ISP accounts) to business email</li>
        <li>Fixing sync issues with OneDrive/SharePoint</li>
        <li>Teams setup for meetings and internal communication</li>
        <li>Basic security best practices and access control</li>
        <li>General “why is this doing that?” Microsoft questions</li>
    </ul>
</section>

<section class="ads-section">
    <h2>Why use a local partner?</h2>
    <p>
        Microsoft 365 is powerful, but full of options and jargon. We bridge the gap between what 
        Microsoft offers and what your team actually needs, in language everyone understands.
    </p>
</section>

<section class="ads-section">
    <h2>Get Microsoft 365 sorted</h2>
    <p>
        Whether you’re starting fresh or trying to untangle a messy setup, SharpLync can help you 
        get back to a clean, reliable foundation.
    </p>
    @include('ads.components.cta-buttons')
</section>
@endsection
