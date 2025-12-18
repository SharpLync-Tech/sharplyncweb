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

            <div class="v-card-grid">

                <article class="v-card">
                    <div class="v-logo-wrap">
                        <img src="{{ asset('images/partners/trendmicro.png') }}" alt="Trend Micro">
                    </div>
                    <h3>Trend Micro</h3>
                    <p>Enterprise-level security and Vision One XDR protection, paired with SharpLync’s practical cybersecurity support.</p>                    
                </article>

                <article class="v-card">
                    <div class="v-logo-wrap">
                        <img src="{{ asset('images/partners/dell.png') }}" alt="Dell Technologies">
                    </div>
                    <h3>Dell Technologies</h3>
                    <p>Business-grade desktops, laptops, servers, and storage solutions.</p>
                </article>

                <article class="v-card">
                    <div class="v-logo-wrap">
                        <img src="{{ asset('images/partners/meraki.png') }}" alt="Cisco Meraki">
                    </div>
                    <h3>Cisco Meraki</h3>
                    <p>Cloud-managed networking, security, SD-WAN, WiFi, and cameras.</p>
                </article>

                <article class="v-card">
                    <div class="v-logo-wrap">
                        <img src="{{ asset('images/partners/hp.png') }}" alt="HP">
                    </div>
                    <h3>HP</h3>
                    <p>Reliable business laptops, desktops, and printing solutions backed by HP’s global reputation.</p>
                </article>

                <article class="v-card">
                    <div class="v-logo-wrap">
                        <img src="{{ asset('images/partners/lenovo.png') }}" alt="Lenovo">
                    </div>
                    <h3>Lenovo</h3>
                    <p>Performance-driven business PCs, workstations, and mobile devices trusted worldwide.</p>
                </article>

                <article class="v-card">
                    <div class="v-logo-wrap">
                       
                        <img src="{{ asset('images/partners/cisco.svg') }}" alt="Cisco">
                    </div>
                    <h3>Cisco</h3>
                    <p>Networking and security solutions trusted globally, from switching to secure remote access.</p>
                </article>

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
            {{-- ===========================
                EXTENDED VENDOR NETWORK (SCROLLING)
            =========================== --}}
            <section class="v-section">
                <div class="v-section-inner">

                    <div class="vendors-hero-panel vendor-ticker-panel">
                        <div class="v-panel-heading">Extended Vendor Network</div>

                        <div class="vendor-ticker vendor-ticker-compact">
                            <div class="vendor-ticker-track">
                                <span>Microsoft</span>
                                <span>Dell</span>
                                <span>Trend Micro</span>
                                <span>Cisco Meraki</span>
                                <span>Adobe</span>
                                <span>Lenovo</span>
                                <span>HP</span>
                                <span>Cisco</span>                    
                                <span>Aruba</span>
                                <span>Palo Alto Networks</span>
                                <span>Fortinet</span>
                                <span>Sophos</span>                    
                                <span>SonicWall</span>
                                <span>Proofpoint</span>
                                <span>Veeam</span>
                                <span>VMware</span>
                                <span>Nutanix</span>
                                <span>NetApp</span>
                                <span>Red Hat</span>
                                <span>APC</span>
                                <span>Eaton</span>
                                <span>Axis</span>
                                <span>Zebra</span>
                                <span>Canon</span>
                                <span>Epson</span>
                                <span>Samsung</span>
                                <span>LG</span>
                                <span>Logitech</span>
                                <span>Jabra</span>
                                <span>Poly</span>
                                <span>EPOS</span>
                                <span>Ergotron</span>
                                <span>Kensington</span>
                                <span>TP-Link</span>
                                <span>Ubiquiti</span>

                                {{-- duplicate for seamless scroll --}}
                                <span>Microsoft</span>
                                <span>Dell</span>
                                <span>Trend Micro</span>
                                <span>Cisco Meraki</span>
                                <span>Adobe</span>
                                <span>Lenovo</span>
                                <span>HP</span>
                                <span>Cisco</span>                    
                                <span>Aruba</span>
                                <span>Palo Alto Networks</span>
                                <span>Fortinet</span>
                                <span>Sophos</span>                    
                                <span>SonicWall</span>
                                <span>Proofpoint</span>
                                <span>Veeam</span>
                                <span>VMware</span>
                                <span>Nutanix</span>
                                <span>NetApp</span>
                                <span>Red Hat</span>
                                <span>APC</span>
                                <span>Eaton</span>
                                <span>Axis</span>
                                <span>Zebra</span>
                                <span>Canon</span>
                                <span>Epson</span>
                                <span>Samsung</span>
                                <span>LG</span>
                                <span>Logitech</span>
                                <span>Jabra</span>
                                <span>Poly</span>
                                <span>EPOS</span>
                                <span>Ergotron</span>
                                <span>Kensington</span>
                                <span>TP-Link</span>
                                <span>Ubiquiti</span>
                            </div>
                        </div>

            <div class="v-panel-foot">
                Vendor availability may vary. SharpLync will always recommend solutions that fit your business needs.
            </div>
        </div>

    </div>
</section>


            <a href="{{ url('/contact') }}" class="v-btn v-btn-primary v-cta-btn">
                Chat with SharpLync
            </a>
        </div>
    </section>

</div>
@endsection