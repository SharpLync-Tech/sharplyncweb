{{-- Google Ads LP: IT Support for Small Business --}}

@extends('ads.layouts.ads-base')

@section('title', 'SharpLync | Small Business IT Support')

@section('content')
<div class="ads-hero">
    <div class="ads-eyebrow">Small Business IT Support • Remote & On-Site</div>
    <h1 class="ads-hero-title">
        Your IT fixed fast.<br>
        <span class="ads-hero-highlight">Local support for busy businesses.</span>
    </h1>
    <p class="ads-hero-subtitle">
        SharpLync provides straightforward IT support for small businesses — from slow computers and Wi-Fi issues 
        to email problems, security, and backup. No jargon, no drama, just problems solved.
    </p>

    @include('ads.components.cta-buttons')
    @include('ads.components.trust-badges')

    <p class="ads-hero-note">
        Same-day remote support available for most issues. On-site support in the Granite Belt and surrounds.
    </p>
</div>

<section class="ads-section">
    <h2>What we can help you with today</h2>
    <p>Most businesses call us when something has stopped working or is driving them mad. Common issues we fix:</p>
    <ul class="ads-list">
        <li>Slow or freezing computers and laptops</li>
        <li>Wi-Fi dropping out or poor coverage across the office</li>
        <li>Email not sending or receiving, lost passwords, account lockouts</li>
        <li>Printers not behaving or not sharing correctly between staff</li>
        <li>Devices full of pop-ups, suspicious messages, or expired antivirus</li>
        <li>One staff member “knows how it works” — and you need a proper setup</li>
    </ul>
</section>

<section class="ads-section">
    <h2>Why small businesses choose SharpLync</h2>
    <ul class="ads-list">
        <li><strong>Local and personal:</strong> You’re not a ticket number — you get a real person who cares.</li>
        <li><strong>No jargon:</strong> Plain-English explanations and clear options, not tech-speak.</li>
        <li><strong>Security-first mindset:</strong> Every job we do considers scams, data protection and safety.</li>
        <li><strong>Microsoft 365 and cloud ready:</strong> We help you get proper, modern tools in place.</li>
        <li><strong>Flexible:</strong> One-off fixes or ongoing support, whatever suits your business.</li>
    </ul>
</section>

<section class="ads-section">
    <h2>How a support job works with SharpLync</h2>
    <ul class="ads-list">
        <li><strong>1. Quick call or form:</strong> You tell us what’s happening in simple terms.</li>
        <li><strong>2. Remote check first:</strong> Where possible, we connect remotely using secure tools.</li>
        <li><strong>3. On-site if needed:</strong> If it needs hands-on work, we come out to your business.</li>
        <li><strong>4. Clear summary:</strong> You get a simple explanation of what we found and fixed.</li>
        <li><strong>5. Options, not pressure:</strong> If deeper issues show up, we’ll explain your options clearly.</li>
    </ul>
</section>

<section class="ads-section">
    <h2>Ready to get your IT sorted?</h2>
    <p>
        Whether you have one computer or a small team, SharpLync can give you stable, secure and reliable 
        IT without the big-business attitude.
    </p>
    @include('ads.components.cta-buttons')
</section>
@endsection
