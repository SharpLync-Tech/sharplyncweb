{{-- =========================================================
     SharpFleet – How It Works
     Layout-first scaffold (placeholder images)
========================================================= --}}

@extends('layouts.sharpfleet')

@section('title', 'How SharpFleet Works')

@section('sharpfleet-content')

<div class="sf-page">

    {{-- ===============================
         Page Intro
    ================================ --}}
    <div class="hero">
        <h1>
            How <span class="highlight">SharpFleet</span> Works
        </h1>

        <p class="hero-intro">
            A quick look at how SharpFleet is used day to day —
            from drivers starting trips to clear, audit-ready reports.
        </p>

        {{-- Anchor list --}}
        <ul class="sf-anchor-list">
            <li><a href="#trips">Start & End Trips</a></li>
            <li><a href="#receipts">Receipt Capture</a></li>
            <li><a href="#no-gps">No GPS, No Hardware</a></li>
            <li><a href="#offline">Works Offline</a></li>
            <li><a href="#reports">Reporting & Compliance</a></li>
            <li><a href="#bookings">Bookings Overview</a></li>
        </ul>
    </div>

    {{-- ===============================
         Section: Start & End Trips
    ================================ --}}
    <section id="trips" class="sf-feature-row">
        <div class="sf-feature-image">
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
        <div class="sf-feature-image">
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
    <section id="no-gps" class="sf-feature-row">
        <div class="sf-feature-image">
            <img src="{{ asset('images/sharpfleet/logo.png') }}" alt="No GPS tracking">
        </div>
        <div class="sf-feature-text">
            <h2>No GPS tracking. No hardware.</h2>
            <p>
                SharpFleet records trips without GPS tracking or vehicle installs,
                avoiding privacy concerns and unnecessary complexity.
            </p>
        </div>
    </section>

    {{-- ===============================
         Section: Offline
    ================================ --}}
    <section id="offline" class="sf-feature-row reverse">
        <div class="sf-feature-image">
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
    <div class="hero">
        <h2>Simple. Practical. Built for real businesses.</h2>
        <p>
            SharpFleet helps businesses stay organised without GPS tracking,
            hardware installs, or complicated workflows.
        </p>
        <a href="/app/sharpfleet/admin/register" class="btn btn-primary">
            Get Started
        </a>
    </div>

</div>

@endsection
