{{-- =========================================================
     SharpFleet – Why SharpFleet
     Simple Accordion / FAQ Style Page
     No JS Required – Mobile & Desktop Friendly
========================================================= --}}

@extends('layouts.sharpfleet')

@section('title', 'Why SharpFleet')

@section('content')

<div class="sf-page">

    {{-- ===============================
         Page Header
    ================================ --}}
    <header class="sf-page-header">
        <h1>Why SharpFleet</h1>
        <p class="sf-page-intro">
            Simple answers to the most common questions about why businesses choose SharpFleet.
            No fluff. No surprises. Just straightforward fleet management.
        </p>
    </header>

    {{-- ===============================
         Accordion Section
    ================================ --}}
    <section class="sf-accordion">

        {{-- Why use SharpFleet --}}
        <details class="sf-accordion-item">
            <summary>
                <span>Why use SharpFleet?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    SharpFleet, unlike other products, is built specifically for real-world businesses that
                    want an easy, reliable way to track trips, vehicles, and compliance without complexity.
                </p>
                <p>
                    It focuses on what actually matters: trip records, driver accountability, reminders,
                    reporting, and offline reliability — not flashy features you’ll never use.
                </p>
            </div>
        </details>

        {{-- Why different --}}
        <details class="sf-accordion-item">
            <summary>
                <span>Why is SharpFleet different from other fleet systems?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    SharpFleet, unlike other products, does not try to be a surveillance platform.
                    There’s no constant tracking, no micromanagement, and no unnecessary data collection.
                </p>
                <p>
                    It’s designed around trust, compliance, and usability — making it easier for drivers
                    to actually use, and easier for businesses to manage.
                </p>
            </div>
        </details>

        {{-- Affordable --}}
        <details class="sf-accordion-item">
            <summary>
                <span>Why is SharpFleet so affordable?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    SharpFleet, unlike other products, includes everything in one affordable price.
                    There are no add-ons, no locked features, and no surprise upgrades.
                </p>
                <p>
                    You pay a simple per-vehicle, per-month cost — often less than a coffee a day —
                    making it accessible for sole traders, not just big fleets.
                </p>
            </div>
        </details>

        {{-- No GPS --}}
        <details class="sf-accordion-item">
            <summary>
                <span>Why doesn’t SharpFleet use GPS tracking?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    SharpFleet, unlike other products, avoids GPS tracking by design.
                    This protects driver privacy and reduces legal, ethical, and compliance concerns.
                </p>
                <p>
                    Trip records are still accurate and compliant — without turning vehicles into
                    tracking devices.
                </p>
            </div>
        </details>

        {{-- NDIS --}}
        <details class="sf-accordion-item">
            <summary>
                <span>Why is SharpFleet ideal for NDIS and Aged Care providers?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    SharpFleet, unlike other products, supports client-based trips, clear records,
                    and audit-friendly reporting without overcomplicating the process.
                </p>
                <p>
                    It works reliably in regional areas, supports offline use, and keeps records simple
                    for compliance and invoicing.
                </p>
            </div>
        </details>

        {{-- Sole traders --}}
        <details class="sf-accordion-item">
            <summary>
                <span>Why is SharpFleet perfect for sole traders and small fleets?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    SharpFleet, unlike other products, is not priced or designed only for large fleets.
                    It scales down just as well as it scales up.
                </p>
                <p>
                    Whether you have one vehicle or twenty, you get the same features and the same
                    simple experience.
                </p>
            </div>
        </details>

        {{-- Offline --}}
        <details class="sf-accordion-item">
            <summary>
                <span>Why does SharpFleet work offline?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    SharpFleet, unlike other products, is designed for real conditions —
                    rural areas, job sites, farms, and regional roads.
                </p>
                <p>
                    Trips can be recorded without coverage and synced automatically when back online.
                </p>
            </div>
        </details>

        {{-- No hardware --}}
        <details class="sf-accordion-item">
            <summary>
                <span>Why don’t I need extra hardware?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    SharpFleet, unlike other products, works entirely on devices you already own.
                    Phones, tablets, or computers — no trackers, no installs, no vehicle downtime.
                </p>
            </div>
        </details>

        {{-- Drivers --}}
        <details class="sf-accordion-item">
            <summary>
                <span>Why is SharpFleet easy for drivers to use?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    SharpFleet, unlike other products, is built mobile-first.
                    Starting and ending a trip takes seconds, not training sessions.
                </p>
            </div>
        </details>

        {{-- Everything included --}}
        <details class="sf-accordion-item">
            <summary>
                <span>Why is everything included with SharpFleet?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    SharpFleet, unlike other products, does not lock features behind tiers.
                    Reporting, reminders, branches, roles, and offline use are all included.
                </p>
                <p>
                    What you see is what you get — simple, honest pricing.
                </p>
            </div>
        </details>

    </section>

</div>

@endsection