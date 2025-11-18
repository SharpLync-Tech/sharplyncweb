@extends('layouts.app')

@section('title', 'SharpLync | Services')

@section('content')

<link rel="stylesheet" href="/css/services.css">

<section class="services-hero">
    <div class="services-hero-inner">
        <img src="/images/sharplync-logo.png" class="hero-logo" alt="SharpLync Logo">

        <h1 class="services-title">What We Do</h1>
        <h2 class="services-subtitle">Sharp <span>Solutions</span></h2>

        <p class="services-tagline">
            From the Granite Belt to the Cloud — smart systems, secure solutions, and real people who care about getting IT right.
        </p>
    </div>

    <img src="/images/hero-circuit.png" class="hero-chip" alt="">
</section>


<section class="services-section services-gradient-bg">

    <div class="service-grid">

        {{-- Remote Support --}}
        <div class="service-card">
            <div class="service-icon">
                <img src="/images/support.png" alt="Remote Support">
            </div>

            <h3 class="service-name">Remote Support</h3>
            <p class="service-desc">
                Instant help wherever you are.
            </p>

            <a href="/services/remote-support" class="service-button">Learn More</a>
        </div>

        {{-- Cybersecurity --}}
        <div class="service-card">
            <div class="service-icon">
                <img src="/images/security.png" alt="Cybersecurity">
            </div>

            <h3 class="service-name">Cybersecurity</h3>
            <p class="service-desc">
                Stay protected 24/7.
            </p>

            <a href="/services/cybersecurity" class="service-button">Learn More</a>
        </div>

    </div>

</section>

@endsection
