{{-- 
    Page: vendors/vendors.blade.php
    Purpose: Central hub listing all SharpLync vendors & partnerships
    Style: Based on Trend Micro page (clean, premium, gradient hero, card grids)
--}}

@extends('layouts.base')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/vendors.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
@endpush

@section('title', 'SharpLync | Our Technology Partners')

@section('content')
<div class="vendors-page">

    {{-- ===========================
         HERO
    ============================ --}}
    <section class="vendors-hero">
        <div class="vendors-hero-inner">

            <div class="vendors-hero-text">
                <p class="v-kicker">SharpLync Vendor Network</p>
                <h1>Trusted partnerships that power SharpLync services.</h1>
                <p class="v-hero-sub">
                    We work closely with world-class technology providers so our customers get reliable,
                    secure, modern IT solutions — backed by real human support from SharpLync.
                </p>
            </div>

            <div class="vendors-hero-panel">
                <div class="v-panel-heading">Why our vendor ecosystem matters</div>
                <ul class="v-panel-list">
                    <li><span class="v-dot"></span> Only trusted, reputable vendors make our partner list</li>
                    <li><span class="v-dot"></span> Official partnerships give us priority support & better pricing</li>
                    <li><span class="v-dot"></span> We recommend what fits your business — not what’s popular</li>
                    <li><span class="v-dot"></span> Every vendor aligns with SharpLync’s security-first standards</li>
                </ul>
                <div class="v-panel-foot">
                    Good tech, chosen deliberately — not by accident.
                </div>
            </div>

        </div>
    </section>



    {{-- ===========================
         OFFICIAL PARTNERS
    ============================ --}}
    <section class="v-section">
        <div class="v-section-inner">
            <h2>Official Partners</h2>
            <p class="v-section-sub">
                These are the vendors SharpLync is officially partnered with, giving our clients
                direct access to certified solutions, priority support, and better value.
            </p>

            <div class="v-card-grid">
                
                {{-- Trend Micro --}}
                <article class="v-card">
                    <div class="v-card-badge">Official Partner</div>
                    <img src="{{ asset('images/partners/tball.png') }}" class="v-logo">
                    <h3>Trend Micro</h3>
                    <p>Enterprise-level security and Vision One XDR protection — paired with SharpLync’s
                       practical, real-world cybersecurity support.</p>
                    <a href="{{ url('/trend-micro') }}" class="v-btn">Explore</a>
                </article>

                {{-- Dell --}}
                <article class="v-card pending">
                    <img src="{{ asset('images/partners/dell.png') }}" class="v-logo">
                    <h3>Dell Technologies</h3>
                    <p>Business-grade desktops, laptops, servers, and storage solutions.</p>
                    <span class="v-pending-label">Pending Approval</span>
                </article>
                

                {{-- Meraki --}}
                <article class="v-card pending">
                    <img src="{{ asset('images/partners/meraki.png') }}" class="v-logo">
                    <h3>Cisco Meraki</h3>
                    <p>Cloud-managed networking, security, SD-WAN, WiFi, and cameras.</p>
                    <span class="v-pending-label">Pending Approval</span>
                </article>


                {{-- HP --}}
                <article class="v-card">
                    <div class="v-card-badge">Official Partner</div>
                    <img src="{{ asset('images/partners/hp.png') }}" class="v-logo">
                    <h3>HP</h3>
                    <p>Reliable business laptops, desktops, and printing solutions backed by HP’s global
                       reputation for quality.</p>
                </article>

                {{-- Lenovo --}}
                <article class="v-card">
                    <div class="v-card-badge">Official Partner</div>
                    <img src="{{ asset('images/partners/lenovo.png') }}" class="v-logo">
                    <h3>Lenovo</h3>
                    <p>Performance-driven business PCs, workstations, and mobile devices trusted by
                       professionals worldwide.</p>
                </article>

                {{-- Cisco --}}
                <article class="v-card">
                    <div class="v-card-badge">Official Partner</div>
                    <img src="{{ asset('images/partners/cisco.png') }}" class="v-logo">
                    <h3>Cisco</h3>
                    <p>Networking and security solutions trusted globally — from switching and routing
                       to secure remote access.</p>
                </article>

            </div>
        </div>
    </section>



    {{-- ===========================
         PENDING / IN PROGRESS
    ============================ --}}
    <section class="v-section v-alt">
        <div class="v-section-inner">
            <h2>Pending Partnerships</h2>
            <p class="v-section-sub">
                These vendors are currently in SharpLync’s onboarding and approval pipeline.
                Once finalised, they’ll move into our Official Partners section.
            </p>

            <div class="v-card-grid">

                {{-- Dell --}}
                <article class="v-card pending">
                    <img src="{{ asset('images/partners/dell.png') }}" class="v-logo">
                    <h3>Dell Technologies</h3>
                    <p>Business-grade desktops, laptops, servers, and storage solutions.</p>
                    <span class="v-pending-label">Pending Approval</span>
                </article>

                {{-- Meraki --}}
                <article class="v-card pending">
                    <img src="{{ asset('images/partners/meraki.png') }}" class="v-logo">
                    <h3>Cisco Meraki</h3>
                    <p>Cloud-managed networking, security, SD-WAN, WiFi, and cameras.</p>
                    <span class="v-pending-label">Pending Approval</span>
                </article>

            </div>
        </div>
    </section>



    {{-- ===========================
         CTA BAND
    ============================ --}}
    <section class="v-cta">
        <div class="v-cta-inner">
            <h2>Want to know which vendors are right for your business?</h2>
            <p>
                We help you choose technology that fits the way you work — not just what’s trending.
            </p>
            <a href="{{ url('/contact') }}" class="v-btn v-btn-primary v-cta-btn">
                Chat with SharpLync
            </a>
        </div>
    </section>

</div>
@endsection
