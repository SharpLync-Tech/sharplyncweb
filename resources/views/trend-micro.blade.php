{{-- 
    Page: trend-micro.blade.php
    Purpose: SharpLync + Trend Micro partnership page
    Style: Business-focused, non-technical, using SharpLync gradient + TM red accents
--}}

@extends('layouts.base')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/trendmicro.css') }}">
@endpush

@section('title', 'SharpLync | Trend Micro Security for Business')

@section('content')
<div class="tm-page">

    {{-- ===========================
         HERO
    ============================ --}}
    <section class="tm-hero">
        <div class="tm-hero-inner">

            <div class="tm-hero-text">
                <p class="tm-kicker">SharpLync + Trend Micro</p>
                <h1>Enterprise-grade security,<br>made local and practical.</h1>
                <p class="tm-hero-sub">
                    As an official Trend Micro partner, SharpLync brings proven, global-class
                    cybersecurity to everyday businesses across the Granite Belt and beyond —
                    without the scare tactics or jargon.
                </p>

                <div class="tm-partner-badge">
                    <div class="tm-logo-wrap">
                        {{-- 🔁 Update the logo path to match your actual asset name --}}
                        <img src="{{ asset('images/partners/trend-micro-partner.png') }}" 
                             alt="Trend Micro Partner Logo">
                    </div>
                    <div class="tm-badge-text">
                        <span class="tm-badge-title">Official Trend Micro Partner</span>
                        <span class="tm-badge-note">Powered by the Trend Micro Vision One™ security platform.</span>
                    </div>
                </div>

                <div class="tm-hero-actions">
                    <a href="{{ url('/contact') }}" class="tm-btn tm-btn-primary">
                        Let’s Talk Security
                    </a>
                    <a href="{{ url('/services') }}" class="tm-btn tm-btn-ghost">
                        Back to Services
                    </a>
                </div>
            </div>

            <div class="tm-hero-panel">
                <div class="tm-panel-heading">
                    Always-on protection for how you really work.
                </div>
                <ul class="tm-panel-list">
                    <li><span class="tm-dot"></span> Devices and laptops protected wherever your team works</li>
                    <li><span class="tm-dot"></span> Email and cloud apps guarded against phishing and scams</li>
                    <li><span class="tm-dot"></span> Smart monitoring to spot suspicious activity early</li>
                    <li><span class="tm-dot"></span> Local support from SharpLync when something doesn’t look right</li>
                </ul>
                <div class="tm-panel-foot">
                    No drama. No panic. Just modern security that quietly does its job.
                </div>
            </div>

        </div>
    </section>

    {{-- ===========================
         WHY SHARPLYNC + TREND MICRO
    ============================ --}}
    <section class="tm-section tm-why">
        <div class="tm-section-inner">
            <h2>Why we chose Trend Micro for our clients</h2>
            <p class="tm-section-sub">
                There are plenty of security products out there. We partner with Trend Micro because
                it gives our clients serious protection, without making day-to-day work harder.
            </p>

            <div class="tm-card-grid">
                <article class="tm-card">
                    <h3>Trusted worldwide, tailored locally</h3>
                    <p>
                        Trend Micro protects millions of devices, networks, and cloud environments
                        globally. We take that same technology and configure it for the way your
                        business actually runs here in the Granite Belt and surrounding regions.
                    </p>
                </article>

                <article class="tm-card">
                    <h3>One platform, less complexity</h3>
                    <p>
                        Instead of juggling separate tools for antivirus, email filtering, and
                        monitoring, Trend Micro Vision One brings everything together. That gives us
                        clearer visibility and faster responses — and gives you fewer things to worry about.
                    </p>
                </article>

                <article class="tm-card">
                    <h3>People-first, not fear-first</h3>
                    <p>
                        We don’t believe in scaring people into buying security. Trend Micro gives
                        us the data, alerts, and protection we need, while SharpLync focuses on
                        plain-English advice and practical steps you can actually follow.
                    </p>
                </article>
            </div>
        </div>
    </section>

    {{-- ===========================
         HOW IT PROTECTS YOUR BUSINESS
    ============================ --}}
    <section class="tm-section tm-how">
        <div class="tm-section-inner">
            <h2>How Trend Micro helps keep your business secure</h2>
            <p class="tm-section-sub">
                We use Trend Micro to quietly cover the core areas where modern attacks usually start.
                You get strong protection in the background, with SharpLync ready to help when you need us.
            </p>

            <div class="tm-feature-grid">
                <article class="tm-feature">
                    <h3>Devices & endpoints</h3>
                    <p>
                        Laptops, desktops, and servers are protected with always-on threat detection,
                        ransomware protection, and smart controls that work wherever your staff log in from.
                    </p>
                </article>

                <article class="tm-feature">
                    <h3>Email & cloud apps</h3>
                    <p>
                        Extra protection around Microsoft 365 and other cloud tools helps stop phishing,
                        malicious links, and suspicious sign-ins before they turn into a problem.
                    </p>
                </article>

                <article class="tm-feature">
                    <h3>Smart threat detection</h3>
                    <p>
                        Trend Micro joins the dots between different signals — devices, email, accounts —
                        so unusual behaviour stands out faster, instead of being lost in the noise.
                    </p>
                </article>

                <article class="tm-feature">
                    <h3>Clear alerts & local help</h3>
                    <p>
                        When something needs attention, SharpLync receives clear, actionable alerts.
                        We investigate, explain what’s happening in normal language, and help you decide
                        on the next steps.
                    </p>
                </article>
            </div>
        </div>
    </section>

    {{-- ===========================
         WHO IT'S GREAT FOR
    ============================ --}}
    <section class="tm-section tm-who">
        <div class="tm-section-inner">
            <h2>Built for real-world organisations</h2>
            <p class="tm-section-sub">
                Whether you’re a growing business, a local NFP, or a school, Trend Micro gives you
                serious protection without needing a full-time security team.
            </p>

            <div class="tm-card-grid">
                <article class="tm-card">
                    <h3>Small & mid-sized businesses</h3>
                    <p>
                        From professional services and trades to retail and warehousing, keep staff
                        protected on the road, in the office, and working from home.
                    </p>
                </article>

                <article class="tm-card">
                    <h3>Not-for-profits & community organisations</h3>
                    <p>
                        Protect sensitive client information, grant data, and day-to-day operations
                        with security that fits tight budgets and busy teams.
                    </p>
                </article>

                <article class="tm-card">
                    <h3>Schools & education</h3>
                    <p>
                        Support safe learning environments with protection for staff devices,
                        admin systems, and cloud tools — backed by a partner who understands
                        education networks.
                    </p>
                </article>
            </div>
        </div>
    </section>

    {{-- ===========================
         CTA BAND
    ============================ --}}
    <section class="tm-cta">
        <div class="tm-cta-inner">
            <h2>Ready to take security off your worry list?</h2>
            <p>
                We’ll review how you work, explain your options in plain English, and recommend
                a Trend Micro-backed security setup that fits your business — no scare tactics, no pressure.
            </p>
            <a href="{{ url('/contact') }}" class="tm-btn tm-btn-primary tm-cta-btn">
                Book a Security Chat with SharpLync
            </a>
        </div>
    </section>

</div>
@endsection
