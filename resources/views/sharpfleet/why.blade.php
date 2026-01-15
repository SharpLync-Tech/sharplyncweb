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
                <span>Why is SharpFleet ideal for NDIS and Aged Care providers?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    Clear records matter. SharpFleet makes it easy to log trips linked to clients,
                    support workers, and services — without overcomplicating the process.
                </p>
                <p>
                    It works reliably in regional areas, supports offline use, and produces
                    audit-friendly records when they’re needed.
                </p>
            </div>
        </details>

        <details class="sf-accordion-item">
            <summary>
                <span>Why is SharpFleet perfect for sole traders and small fleets?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    SharpFleet scales with you. Whether you run one vehicle or twenty,
                    the experience stays simple and consistent.
                </p>
                <p>
                    You’re not paying for enterprise features you don’t need — just practical
                    tools that support day-to-day operations.
                </p>
            </div>
        </details>

        <details class="sf-accordion-item">
            <summary>
                <span>Why does SharpFleet work offline?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    Not all work happens in areas with reliable mobile coverage.
                    SharpFleet is built to handle rural, regional, and job-site conditions.
                </p>
                <p>
                    Trips can be recorded offline and automatically synced once connectivity
                    is restored — no data lost.
                </p>
            </div>
        </details>

        <details class="sf-accordion-item">
            <summary>
                <span>Why don’t I need extra hardware?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    SharpFleet works on devices you already own — phones, tablets, and computers.
                </p>
                <p>
                    There’s no need for trackers, installations, or vehicle downtime to get started.
                </p>
            </div>
        </details>

        <details class="sf-accordion-item">
            <summary>
                <span>Why is SharpFleet easy for drivers to use?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    The system is designed to be quick and intuitive. Starting or ending a trip
                    takes seconds, not training sessions.
                </p>
                <p>
                    Less friction means better data and fewer missed entries.
                </p>
            </div>
        </details>

        <details class="sf-accordion-item">
            <summary>
                <span>Why is everything included with SharpFleet?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    SharpFleet keeps pricing honest. Reporting, reminders, safety checks,
                    branches, and offline use are all part of the same subscription.
                </p>
                <p>
                    What you see is what you get — no feature lockouts and no upsell pressure.
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
            SharpFleet is designed for tradies, service businesses, NDIS providers,
            and small fleets who want clarity without complexity.
        </p>
        <a href="/app/sharpfleet/admin/register" class="btn btn-primary">
            Get Started
        </a>
    </div>

</div>

@endsection
