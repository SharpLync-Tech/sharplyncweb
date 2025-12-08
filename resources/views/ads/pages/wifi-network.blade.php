{{-- Google Ads LP: Network & Wi-Fi --}}

@extends('ads.layouts.ads-base')

@section('title', 'SharpLync | Business Wi-Fi & Network Support')

@section('content')
<div class="ads-hero">
    <div class="ads-eyebrow">Wi-Fi • Network • Internet Issues</div>
    <h1 class="ads-hero-title">
        Fix your Wi-Fi and network issues,<br>
        <span class="ads-hero-highlight">so the whole team can work again.</span>
    </h1>
    <p class="ads-hero-subtitle">
        Dropping out, slow in certain rooms, or printers disappearing from the network? 
        SharpLync helps small businesses stabilise their Wi-Fi and network so staff can get on with their work.
    </p>

    @include('ads.components.cta-buttons')
    @include('ads.components.trust-badges')
</div>

<section class="ads-section">
    <h2>Common problems we fix</h2>
    <ul class="ads-list">
        <li>Wi-Fi drops out randomly during the day</li>
        <li>Great Wi-Fi near the modem, terrible at the other end of the building</li>
        <li>Some devices won’t stay connected or keep asking for passwords</li>
        <li>Printers or scanners disappearing from the network</li>
        <li>Staff using personal hotspots to get reliable internet</li>
    </ul>
</section>

<section class="ads-section">
    <h2>How we approach it</h2>
    <ul class="ads-list">
        <li>Check how your equipment is set up now</li>
        <li>Test Wi-Fi performance in different areas</li>
        <li>Look at interference, coverage and device placement</li>
        <li>Recommend changes — from simple tweaks to better hardware</li>
        <li>Help you plan for future growth or extra sites</li>
    </ul>
</section>

<section class="ads-section">
    <h2>Get your Wi-Fi working properly</h2>
    <p>
        Stop fighting with your network. A quick assessment can reveal why things are unstable 
        and what’s needed to stabilise them for good.
    </p>
    @include('ads.components.cta-buttons')
</section>
@endsection
