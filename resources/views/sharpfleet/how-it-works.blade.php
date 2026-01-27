{{-- =========================================================
     SharpFleet - How It Works
     Layout-first scaffold (placeholder images)
========================================================= --}}

@extends('layouts.sharpfleet')

@section('title', 'How SharpFleet Works')

@section('sharpfleet-content')

<div class="sf-page sf-page--plain">

    {{-- ===============================
         Page Intro
    ================================ --}}
    <div class="sf-hero">
        <h1>How <span class="highlight">SharpFleet</span> Works</h1>

        <p class="sf-hero-intro">
            A quick look at how SharpFleet is used day to day --
            from drivers starting trips to clear, audit-ready reports.
        </p>
    </div>

    <nav class="sf-anchor-nav" aria-label="How it works sections">
        <ul class="sf-anchor-list">
            <li><a href="#trips">Start &amp; End Trips</a></li>
            <li><a href="#receipts">Receipt Capture</a></li>
            <li><a href="#no-gps">No GPS, No Hardware</a></li>
            <li><a href="#offline">Works Offline</a></li>
            <li><a href="#reports">Reporting &amp; Compliance</a></li>
            <li><a href="#bookings">Bookings Overview</a></li>
        </ul>
    </nav>

    {{-- ===============================
         Section: Start & End Trips
    ================================ --}}
    <section id="trips" class="sf-feature-row">
        <div class="sf-feature-image sf-feature-image--phone">
            <img src="{{ asset('images/sharpfleet/logo.png') }}" alt="Start and end trips">
        </div>
        <div class="sf-feature-text">
            <h2>Drivers start and end trips in seconds</h2>
            <p>
                Drivers record trips directly from their phone using a simple,
                guided flow designed to minimise missed or incomplete entries.
            </p>
        </div>
    </section>

    {{-- ===============================
         Section: Receipt Capture
    ================================ --}}
    <section id="receipts" class="sf-feature-row reverse">
        <div class="sf-feature-image sf-feature-image--phone">
            <img src="{{ asset('images/sharpfleet/logo.png') }}" alt="Receipt capture">
        </div>
        <div class="sf-feature-text">
            <h2>Capture receipts as you go</h2>
            <p>
                Drivers can attach receipts to trips at the time they occur,
                keeping supporting records linked and easy to find later.
            </p>
        </div>
    </section>

    {{-- ===============================
         Section: No GPS
    ================================ --}}
    <section id="offline" class="sf-feature-row">
        <div class="sf-feature-image sf-feature-image--phone">
            <img src="{{ asset('images/sharpfleet/logo.png') }}" alt="Offline mode">
        </div>
        <div class="sf-feature-text">
            <h2>Works even when offline</h2>
            <p>
                Trips can still be recorded without mobile coverage and will
                automatically sync once the device is back online.
            </p>
        </div>
    </section>

    {{-- ===============================
         Section: Reporting
    ================================ --}}
    <section id="reports" class="sf-feature-row">
        <div class="sf-feature-image">
            <img src="{{ asset('images/sharpfleet/logo.png') }}" alt="Reporting and compliance">
        </div>
        <div class="sf-feature-text">
            <h2>Clear reports for compliance and audits</h2>
            <p>
                Generate clean, easy-to-read reports covering vehicle usage,
                business vs private travel, and client-related trips.
            </p>
        </div>
    </section>

    {{-- ===============================
         Section: Bookings
    ================================ --}}
    <section id="bookings" class="sf-feature-row reverse">
        <div class="sf-feature-image">
            <img src="{{ asset('images/sharpfleet/logo.png') }}" alt="Bookings views">
        </div>
        <div class="sf-feature-text">
            <h2>Vehicle bookings at a glance</h2>
            <p>
                View bookings using day, week, or month layouts to understand
                availability and avoid clashes across shared vehicles.
            </p>
        </div>
    </section>

    {{-- ===============================
         CTA
    ================================ --}}
    <div class="sf-hero sf-hero-cta">
        <h2>Simple. Practical. Built for real businesses.</h2>
        <p>
            SharpFleet helps businesses stay organised without GPS tracking,
            hardware installs, or complicated workflows.
        </p>
        <a href="/app/sharpfleet/admin/register" class="sf-cta">
            Get Started
        </a>
    </div>

</div>

@endsection

@push('styles')
<style>
    .sf-page {
        display: flex;
        flex-direction: column;
        gap: 36px;
        padding: 8px 0 32px;
    }

    .sf-page--plain {
        background: #ffffff;
        padding: 16px 20px 40px;
        border-radius: 18px;
    }

    .sf-hero {
        padding: 8px 0;
    }

    .sf-hero h1,
    .sf-hero h2 {
        margin-bottom: 10px;
    }

    .sf-hero-intro {
        max-width: 720px;
        margin: 0;
    }

    .sf-anchor-nav {
        position: sticky;
        top: 86px;
        z-index: 2;
        background: rgba(255, 255, 255, 0.9);
        border-bottom: 1px solid rgba(10, 42, 77, 0.1);
        padding: 10px 0 14px;
        backdrop-filter: blur(6px);
    }

    .sf-anchor-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 16px;
        list-style: none;
        padding: 0;
        margin: 0;
        font-weight: 600;
    }

    .sf-anchor-list a {
        color: #0A2A4D;
        text-decoration: none;
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(44, 191, 174, 0.12);
    }

    .sf-anchor-list a:hover {
        background: rgba(44, 191, 174, 0.22);
    }

    .sf-feature-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.1fr);
        gap: 24px;
        align-items: center;
        padding: 8px 0;
        scroll-margin-top: 120px;
    }

    .sf-feature-row + .sf-feature-row {
        border-top: 1px solid rgba(10, 42, 77, 0.08);
        padding-top: 28px;
        margin-top: 8px;
    }

    .sf-feature-row.reverse {
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
    }

    .sf-feature-row.reverse .sf-feature-image {
        order: 2;
    }

    .sf-feature-row.reverse .sf-feature-text {
        order: 1;
    }

    .sf-feature-image {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .sf-feature-image img {
        width: 100%;
        max-width: 360px;
        height: auto;
        border-radius: 12px;
        box-shadow: 0 8px 18px rgba(10, 42, 77, 0.08);
    }

    .sf-feature-image--phone img {
        max-width: 300px;
        aspect-ratio: 9 / 16;
        height: auto;
        object-fit: cover;
    }

    .sf-feature-text h2 {
        margin-bottom: 10px;
    }

    .sf-feature-text p {
        margin: 0;
    }

    .sf-hero-cta {
        text-align: center;
    }

    @media (max-width: 900px) {
        .sf-anchor-nav {
            top: 72px;
        }

        .sf-feature-row,
        .sf-feature-row.reverse {
            grid-template-columns: 1fr;
        }

        .sf-feature-row.reverse .sf-feature-image,
        .sf-feature-row.reverse .sf-feature-text {
            order: initial;
        }

        .sf-feature-image img {
            max-width: 100%;
        }
    }
</style>
@endpush

