{{-- Google Ads LP: Backup & Recovery --}}

@extends('ads.layouts.ads-base')

@section('title', 'SharpLync | Data Backup & Recovery')

@section('content')
<div class="ads-hero">
    <div class="ads-eyebrow">Backup • Recovery • Ransomware Protection</div>
    <h1 class="ads-hero-title">
        Protect your business data<br>
        <span class="ads-hero-highlight">before</span> you wish you had.
    </h1>
    <p class="ads-hero-subtitle">
        SharpLync helps small businesses put sensible backup and recovery in place, so a failed hard drive, 
        staff mistake or cyber incident doesn’t wipe out critical information.
    </p>

    @include('ads.components.cta-buttons')
    @include('ads.components.trust-badges')
</div>

<section class="ads-section">
    <h2>Backup problems we commonly see</h2>
    <ul class="ads-list">
        <li>“I think it’s backing up, but I’ve never checked it.”</li>
        <li>Old USB drives used as “backup” — never tested or rotated</li>
        <li>No off-site or cloud copy of important files</li>
        <li>Single point of failure: everything lives on one device</li>
        <li>Backups running, but no idea how to restore</li>
    </ul>
</section>

<section class="ads-section">
    <h2>What we can set up</h2>
    <ul class="ads-list">
        <li>Workstation and laptop backups</li>
        <li>Simple and secure cloud-based backup options</li>
        <li>Versioning so you can roll back to earlier copies</li>
        <li>Basic recovery plan so you know who does what if things fail</li>
        <li>Regular checks to confirm backups are actually working</li>
    </ul>
</section>

<section class="ads-section">
    <h2>Start with a quick backup check</h2>
    <p>
        We’ll review what you have in place now (if anything), identify gaps, and give you options that are realistic for 
        your size and budget.
    </p>
    @include('ads.components.cta-buttons')
</section>
@endsection
