{{-- 
    Page: vendors/vendors.blade.php
    Purpose: Central hub listing all SharpLync vendors & partnerships
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
                    secure, modern IT solutions, backed by real human support from SharpLync.
                </p>
            </div>

            <div class="vendors-hero-panel">
                <div class="v-panel-heading">Why our vendor ecosystem matters</div>
                <ul class="v-panel-list">
                    <li><span class="v-dot"></span> Only trusted, reputable vendors make our partner list</li>
                    <li><span class="v-dot"></span> Official partnerships give us priority support & better pricing</li>
                    <li><span class="v-dot"></span> We recommend what fits your business</li>
                    <li><span class="v-dot"></span> Security-first standards across all platforms</li>
                </ul>
                <div class="v-panel-foot">
                    Good tech, chosen deliberately, not by accident.
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
            {{-- Featured Microsoft Partner --}}
                <article class="v-card v-card-featured">
                    <div class="v-logo-wrap">
                        <img src="{{ asset('images/partners/microsoft.png') }}" alt="Microsoft">
                    </div>
                    <h3>Microsoft</h3>
                    <p>
                        Cloud productivity, identity, and infrastructure solutions including
                        Microsoft 365 and Azure, delivered with practical setup, security,
                        and ongoing support from SharpLync.
                    </p>
                </article>

            <div class="v-card-grid">

                @php
                    $partners = [
                        ['trendmicro.png','Trend Micro','Enterprise-level security and Vision One XDR protection.'],
                        ['dell.png','Dell Technologies','Business-grade desktops, laptops, servers, and storage.'],
                        ['meraki.png','Cisco Meraki','Cloud-managed networking, security, SD-WAN, WiFi, cameras.'],
                        ['hp.png','HP','Reliable business laptops, desktops, and printing solutions.'],
                        ['lenovo.png','Lenovo','Performance-driven business PCs and workstations.'],
                        ['cisco.svg','Cisco','Networking and security solutions trusted globally.'],
                    ];
                @endphp

                @foreach($partners as $p)
                <article class="v-card">
                    <div class="v-logo-wrap">
                        <img src="{{ asset('images/partners/'.$p[0]) }}" alt="{{ $p[1] }}">
                    </div>
                    <h3>{{ $p[1] }}</h3>
                    <p>{{ $p[2] }}</p>
                </article>
                @endforeach

            </div>
        </div>
    </section>

    {{-- ===========================
         EXTENDED VENDOR NETWORK (FULL WIDTH)
    ============================ --}}
    <section class="vendor-ticker-band">

        <div class="vendor-ticker-inner">
            <div class="vendor-ticker-title">Extended Vendor Network</div>

            <div class="vendor-ticker">
                <div class="vendor-ticker-track">
                    @php
                        @php
                            $vendors = [
                                // Anchor vendors (credibility first)
                                'Microsoft',
                                'Adobe',
                                'Dell',
                                'Trend Micro',
                                'Cisco Meraki',
                                'Lenovo',
                                'HP',

                                // Core infrastructure & networking
                                'Cisco',
                                'HPE',
                                'VMware',
                                'Veeam',
                                'Palo Alto Networks',
                                'Fortinet',
                                'Sophos',
                                'SonicWall',
                                'Proofpoint',
                                'Aruba',

                                // Data & platform
                                'Nutanix',
                                'NetApp',
                                'Red Hat',

                                // Power, peripherals & devices
                                'APC',
                                'Eaton',
                                'Axis',
                                'Zebra',
                                'Canon',
                                'Epson',
                                'Samsung',
                                'LG',

                                // Workplace & accessories
                                'Logitech',
                                'Jabra',
                                'Poly',
                                'EPOS',
                                'Ergotron',
                                'Kensington',

                                // SMB / networking extras
                                'TP-Link',
                                'Ubiquiti'
                            ];
                        @endphp


                    @foreach(array_merge($vendors, $vendors) as $vendor)
                        <span>{{ $vendor }}</span>
                    @endforeach
                </div>
            </div>

            <div class="vendor-ticker-foot">
                Vendor availability may vary. SharpLync will always recommend solutions that fit your business needs.
            </div>
        </div>

    </section>

    {{-- ===========================
         CTA
    ============================ --}}
    <section class="v-cta">
        <div class="v-cta-inner">
            <h2>Want to know which vendors are right for your business?</h2>
            <p>We help you choose technology that fits how you work, not just what’s trending.</p>
            <a href="{{ url('/contact') }}" class="v-btn v-btn-primary v-cta-btn">
                Chat with SharpLync
            </a>
        </div>
    </section>

</div>
@endsection
