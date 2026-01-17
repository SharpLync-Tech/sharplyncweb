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

                <ul style="list-style: none; padding-left: 0; margin-top: 1rem;">
                    <li style="display: flex; align-items: flex-start; gap: 0.6rem; margin-bottom: 0.5rem;">
                        <span>✔</span>
                        <span>Simple trip logging for vehicles and drivers</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 0.6rem; margin-bottom: 0.5rem;">
                        <span>✔</span>
                        <span>Clear, audit-friendly reporting</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 0.6rem; margin-bottom: 0.5rem;">
                        <span>✔</span>
                        <span>No GPS tracking or driver surveillance</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 0.6rem; margin-bottom: 0.5rem;">
                        <span>✔</span>
                        <span>Works online and offline</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 0.6rem; margin-bottom: 0.5rem;">
                        <span>✔</span>
                        <span>No hardware or vehicle installations</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 0.6rem; margin-bottom: 0.5rem;">
                        <span>✔</span>
                        <span>Support for shared and pool vehicles</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 0.6rem; margin-bottom: 0.5rem;">
                        <span>✔</span>
                        <span>Optional safety checks and reminders</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 0.6rem; margin-bottom: 0.5rem;">
                        <span>✔</span>
                        <span>Private vehicle trips for real-world exceptions</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 0.6rem; margin-bottom: 0.5rem;">
                        <span>✔</span>
                        <span>Flat, affordable pricing per vehicle</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 0.6rem;">
                        <span>✔</span>
                        <span>Designed for small and growing fleets</span>
                    </li>
                </ul>
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
