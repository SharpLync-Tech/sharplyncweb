@extends('layouts.sharpfleet')

@section('title', 'SharpFleet – Simple Fleet Management for Real Businesses')

@push('styles')
    <link rel="stylesheet" href="{{ secure_asset('css/sharpfleet/sharpfleet-home.css') }}?v={{ @filemtime(public_path('css/sharpfleet/sharpfleet-home.css')) ?: time() }}">
@endpush

@section('sharpfleet-content')

    {{-- =========================
         HERO
    ========================== --}}
    <div class="hero">
        <h1>
            Fleet Management:<br>
            <span class="highlight">Sorted from Start to Finish</span>
        </h1>

        {{-- =========================
             RUNNING VEHICLE BANNER
        ========================== --}}
        <section class="fleet-banner">
            <p class="fleet-banner-label">
                Works with cars, utes, trucks, plant, and more
            </p>

            <div class="fleet-banner-track">
                <div class="fleet-banner-row">
                    @for ($i = 1; $i <= 10; $i++)
                        <img
                            src="{{ asset('images/sharpfleet/' . $i . '.png') }}"
                            alt="SharpFleet supported vehicle {{ $i }}"
                            loading="lazy"
                        >
                    @endfor

                    @for ($i = 1; $i <= 10; $i++)
                        <img
                            src="{{ asset('images/sharpfleet/' . $i . '.png') }}"
                            alt=""
                            aria-hidden="true"
                        >
                    @endfor
                </div>
            </div>
        </section>

        <p class="hero-intro">
            Logbooks for distance and engine hours.<br>
            No GPS. No hardware. No micromanagement.
        </p>

        <p class="hero-price">
            All features included for only <strong>AUD3.50</strong> per vehicle, per month.
        </p>

        <div class="hero-bam">
            <p>Work vehicles? <strong class="text-primary">Covered.</strong></p>
            <p>Plant and equipment? <strong class="text-primary">Covered.</strong></p>
            <p>Bookings, receipts, compliance? <strong class="text-primary">Covered.</strong></p>
        </div>

        <a href="/app/sharpfleet/admin/register" class="sf-cta">
            Get Started
        </a>

        <p class="mt-2 small text-white">
            Already have an account?
            <a href="/app/sharpfleet/login" class="text-primary">Sign in here</a>
        </p>
    </div>

    {{-- =========================
         FEATURES
    ========================== --}}
    <section class="mb-4 hero-features">
        <div class="text-center mb-4">
            <h2 class="card-title" style="color: var(--text-light);">
                Everything You Need — Nothing You Don’t
            </h2>
            <p class="mb-0 max-w-700 mx-auto text-white">
                :contentReference[oaicite:0]{index=0} is built around how vehicles are actually used day to day —
                trips, drivers, bookings, and paperwork — without surveillance or complexity.
            </p>
        </div>

        <div class="sf-home-features-wrap">

            <div class="sf-home-features-grid">

                <div class="card">
                    <div class="card-header">
                        <h3>🚗 Trip & Logbook Tracking</h3>
                    </div>
                    <p>
                        Record trips, purposes, distance, and engine hours as they happen —
                        not guessed weeks later. Fully compliant logs without GPS tracking.
                    </p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>👥 Drivers & Vehicles</h3>
                    </div>
                    <p>
                        Assign drivers, manage permissions, and link them to vehicles or plant.
                        Your rules, your workflows — SharpFleet adapts to you.
                    </p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>📅 Bookings & Scheduling</h3>
                    </div>
                    <p>
                        Book vehicles and equipment in advance so teams know what’s available.
                        Avoid double-ups, downtime, and last-minute phone calls.
                    </p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>🧾 Receipts & Fuel Capture</h3>
                    </div>
                    <p>
                        Capture fuel and expense receipts directly against vehicles and trips.
                        No shoeboxes, no lost paperwork — everything stored with the right records.
                    </p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>🦺 Safety & Compliance</h3>
                    </div>
                    <p>
                        Create safety checks that match your operation.
                        Keep clear records that protect drivers and demonstrate due diligence.
                    </p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>📱 Works Anywhere</h3>
                    </div>
                    <p>
                        Use SharpFleet on phones, tablets, and desktops —
                        even with poor reception. No hardware installs, no training headaches.
                    </p>
                </div>

            </div>

            <div class="sf-home-pricing-row">
                <div class="card sf-home-pricing-card">
                    <div class="card-header">
                        <h3>🏷️ Simple, Scalable Pricing</h3>
                    </div>

                    <div class="card-body">
                        <p>
                            <strong>AUD3.50 per vehicle per month</strong><br>
                            Covers your first <strong>10 vehicles</strong>
                        </p>

                        <p>
                            <strong>AUD2.50 per vehicle per month</strong><br>
                            For vehicles <strong>11–20</strong>
                        </p>

                        <p>
                            <strong>20+ vehicles?</strong><br>
                            <a href="/contact">Contact us for tailored pricing</a>
                        </p>

                        <p class="sf-home-pricing-note">
                            No long-term contracts. Cancel anytime.
                            Your data and historical logs always remain accessible.
                            (International customers billed in local currency via Stripe.)
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- =========================
         CTA
    ========================== --}}
    <div class="hero">
        <h2>Built for Real-World Businesses</h2>
        <p>
            SharpFleet is designed for tradies, service businesses,
            small fleets, and growing teams who want control —
            without micromanagement.
        </p>
        <a href="/app/sharpfleet/admin/register" class="sf-cta">
            Start Your Free Trial
        </a>
    </div>

@endsection
