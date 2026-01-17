{{-- =========================================================
     SharpFleet – Why SharpFleet
     Accordion / FAQ Page (Refactored Copy)
========================================================= --}}

@extends('layouts.sharpfleet')

@section('title', 'Why SharpFleet')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/sharpfleet/why-sharpfleet.css') }}">
@endpush

@section('sharpfleet-content')

<div class="sf-page">

    {{-- ===============================
         Page Header
    ================================ --}}
    <div class="hero">
        <h1>
            Why <span class="highlight">SharpFleet</span>
        </h1>

        <p class="hero-intro">
            Simple answers to the most common questions about why businesses choose SharpFleet.
            No fluff. No surprises. Just straightforward fleet management.
        </p>

        {{-- ===============================
             Feature Snapshot (FIXED)
        ================================ --}}
        <div class="sf-feature-list">
            <p>Simple trip logging for vehicles and drivers</p>
            <p>Clear, audit-friendly reporting</p>
            <p>No GPS tracking or driver surveillance</p>
            <p>Works online and offline</p>
            <p>No hardware or vehicle installations</p>
            <p>Support for shared and pool vehicles</p>
            <p>Optional safety checks and reminders</p>
            <p>Private vehicle trips for real-world exceptions</p>
            <p>Flat, affordable pricing per vehicle</p>
            <p>Designed for small and growing fleets</p>
        </div>
    </div>

    {{-- ===============================
         Accordion Section
    ================================ --}}
    <section class="sf-accordion mb-4">

        <details class="sf-accordion-item">
            <summary>
                <span>Why use SharpFleet?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    SharpFleet was built for real-world businesses that want clear, reliable fleet records
                    without complexity or micromanagement.
                </p>
                <p>
                    It focuses on trips, compliance, and accountability — making it easier for drivers to
                    do the right thing and for businesses to stay organised.
                </p>
            </div>
        </details>

        <details class="sf-accordion-item">
            <summary>
                <span>Why is SharpFleet different from other fleet systems?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    Many fleet systems are built for large enterprises, packed with features most small
                    businesses never use.
                </p>
                <p>
                    SharpFleet takes a simpler approach — focusing on the tools businesses actually need,
                    without GPS surveillance, expensive hardware, or rigid workflows.
                </p>
            </div>
        </details>

        <details class="sf-accordion-item">
            <summary>
                <span>Why is SharpFleet so affordable?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    Pricing is simple and transparent. You pay a flat, affordable rate per vehicle,
                    per month — and everything is included.
                </p>
                <p>
                    There are no feature tiers, no add-ons, and no surprise upgrades as your business grows.
                    For many customers, it works out to less than a coffee a day.
                </p>
            </div>
        </details>

        <details class="sf-accordion-item">
            <summary>
                <span>Why doesn’t SharpFleet use GPS tracking?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    SharpFleet is designed around trust and privacy. Drivers aren’t constantly tracked,
                    and vehicles aren’t turned into monitoring devices.
                </p>
                <p>
                    You still get accurate, compliant trip records — without raising privacy concerns
                    or creating unnecessary tension with staff.
                </p>
            </div>
        </details>

        <details class="sf-accordion-item">
            <summary>
                <span>What if a driver needs to use their own vehicle?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    Sometimes fleet vehicles aren’t available. SharpFleet supports this by allowing
                    organisations to record trips made using personal vehicles — when enabled by an admin.
                </p>
                <p>
                    Personal vehicle trips are designed for occasional, real-world exceptions and are
                    recorded for reporting and reimbursement purposes, without adding personal cars
                    as fleet assets.
                </p>
                <p>
                    To keep things fair, private vehicle usage is limited in proportion to your subscribed
                    fleet size and is not intended to replace fleet vehicles or avoid subscriptions.
                </p>
            </div>
        </details>

        <details class="sf-accordion-item">
            <summary>
                <span>Why does SharpFleet work for service-based businesses?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    Clear records matter in any service-based operation. SharpFleet makes it easy to log
                    trips linked to customers, jobs, or work activities without slowing teams down.
                </p>
                <p>
                    It supports real-world working conditions, including shared vehicles and offline use,
                    while producing audit-friendly records when needed.
                </p>
            </div>
        </details>

    </section>

    {{-- ===============================
         CTA
    ================================ --}}
    <div class="hero">
        <h2>Built for Real Businesses</h2>
        <p>
            SharpFleet is designed for tradies, service teams, and growing fleets
            who want clarity without complexity.
        </p>
        <a href="/app/sharpfleet/admin/register" class="btn btn-primary">
            Get Started
        </a>
    </div>

</div>

@endsection
