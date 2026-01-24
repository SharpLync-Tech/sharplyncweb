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

        {{-- WHY USE --}}
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
                    @php
                        $features = [
                            'Simple trip logging for vehicles and drivers',
                            'Clear, audit-friendly reporting',
                            'Fuel receipt capture with photo uploads, linked directly to vehicles',
                            'No GPS tracking or driver surveillance',
                            'Works online and offline',
                            'No hardware or vehicle installations',
                            'Support for shared and pool vehicles',
                            'Optional safety checks and reminders',
                            'Private vehicle trips for real-world exceptions',
                            'Can be used to track hours on equipment, machinery, and plant',
                            'Flat, affordable pricing per vehicle',
                            'Designed for small and growing fleets',
                            
                        ];
                    @endphp

                    @foreach ($features as $feature)
                        <li style="display: flex; align-items: flex-start; gap: 0.6rem; margin-bottom: 0.5rem;">
                            <span aria-hidden="true" class="sf-tick">✔</span>
                            <span>{{ $feature }}</span>
                        </li>

                    @endforeach
                </ul>

                <p style="margin-top: 0.75rem;">
                    This makes SharpFleet suitable not just for vehicles, but also for tracking usage hours
                    on equipment where time-based records matter more than distance.
                </p>
            </div>
        </details>

        {{-- DIFFERENT --}}
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
                    SharpFleet takes a simpler approach: focusing on the tools businesses actually need,
                    without GPS surveillance, expensive hardware, or rigid workflows.
                </p>
            </div>
        </details>

        {{-- FUEL RECEIPTS --}}
        <details class="sf-accordion-item">
            <summary>
                <span>Why does SharpFleet support fuel receipt capture?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>

            <div class="sf-accordion-content">
                <p>
                    Fuel is one of the most common fleet expenses, and one of the easiest to lose track of.
                    SharpFleet allows drivers to capture a fuel receipt at the time of purchase,
                    directly from their phone.
                </p>

                <p>
                    Each receipt is stored with the vehicle, date, and odometer reading,
                    creating a clear audit trail without extra paperwork or manual follow-ups.
                </p>

                <p>
                    Receipts can also be automatically emailed to accounts,
                    reducing lost receipts and end-of-month chasing.
                </p>

                <p>
                    Like the rest of SharpFleet, fuel receipt capture is designed to be quick for drivers
                    and useful for the business, without turning into an expense management system.
                </p>
            </div>
        </details>


        {{-- AFFORDABLE --}}
        <details class="sf-accordion-item">
            <summary>
                <span>Why is SharpFleet so affordable?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    Pricing is simple and transparent. You pay a flat, affordable rate per vehicle,
                    per month, and everything is included.
                </p>
                <p>
                    There are no feature tiers, no add-ons, and no surprise upgrades as your business grows.
                    For many customers, it works out to be less than a coffee a day.
                </p>
            </div>
        </details>

        {{-- GPS --}}
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
                    You still get accurate, compliant trip records, without raising privacy concerns
                    or creating unnecessary tension with staff.
                </p>
            </div>
        </details>

        {{-- PRIVATE VEHICLE --}}
        <details class="sf-accordion-item">
            <summary>
                <span>What if a driver needs to use their own vehicle?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    Sometimes fleet vehicles aren’t available. SharpFleet supports this by allowing
                    organisations to record trips made using personal vehicles, when enabled by a company administrator.
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

        {{-- SERVICE BUSINESSES --}}
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
                    It supports real-world working conditions, including shared vehicles, equipment usage,
                    and offline use, while producing audit-friendly records when needed.
                </p>
            </div>
        </details>

        {{-- OFFLINE --}}
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
                    is restored, no data lost.
                </p>
            </div>
        </details>

        {{-- HARDWARE --}}
        <details class="sf-accordion-item">
            <summary>
                <span>Why don’t I need extra hardware?</span>
                <span class="sf-accordion-icon">+</span>
            </summary>
            <div class="sf-accordion-content">
                <p>
                    SharpFleet works on devices you already own;phones, tablets, and computers.
                </p>
                <p>
                    There’s no need for trackers, installations, or vehicle downtime to get started.
                </p>
            </div>
        </details>

        {{-- DRIVERS --}}
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

        {{-- EVERYTHING INCLUDED --}}
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
                    What you see is what you get, no feature lockouts and no upsell pressure.
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
        <a href="/app/sharpfleet/admin/register" class="sf-cta">
            Get Started
        </a>
    </div>

</div>

@endsection
