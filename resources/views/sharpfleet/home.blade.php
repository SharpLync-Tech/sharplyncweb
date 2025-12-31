@extends('layouts.sharpfleet')

@section('title', 'SharpFleet – Simple Fleet Management for Real Businesses')

@section('sharpfleet-content')

    {{-- =========================
         HERO
    ========================== --}}
    <div class="hero">
        <h1>
            Fleet Management,<br>
            <span class="highlight">Without the Headaches</span>
        </h1>

        <p class="hero-intro">
            Logbooks for kilometres and engine hours.<br>
            No GPS. No hardware. No micromanagement.
        </p>

        <div class="hero-bam">
            <p>Work vehicles? <strong class="text-primary">Yep — we’ve got you.</strong></p>
            <p>Plant and equipment? <strong class="text-primary">Yep — we’ve got you.</strong></p>
            <p>Client visits or job runs? <strong class="text-primary">Yep — we’ve got you too.</strong></p>
        </div>

        <p class="hero-price">
            From <strong>$3.50</strong> per month.
        </p>

        <a href="/app/sharpfleet/admin/register" class="btn btn-primary">
            Get Started
        </a>

        <p class="mt-2 small text-white">
            Already have an account?
            <a href="/app/sharpfleet/login" class="text-primary">Sign in here</a>
        </p>
    </div>

    {{-- =========================
         RUNNING VEHICLE BANNER
    ========================== --}}
    <section class="fleet-banner">
        <p class="fleet-banner-label">
            Works with cars, utes, trucks, plant, and more
        </p>

        <div class="fleet-banner-track">
            <div class="fleet-banner-row">
                {{-- First set --}}
                @for ($i = 1; $i <= 9; $i++)
                    <img
                        src="{{ asset('images/sharpfleet/' . $i . '.png') }}"
                        alt="SharpFleet supported vehicle {{ $i }}"
                        loading="lazy"
                    >
                @endfor

                {{-- Duplicate set for seamless loop --}}
                @for ($i = 1; $i <= 9; $i++)
                    <img
                        src="{{ asset('images/sharpfleet/' . $i . '.png') }}"
                        alt=""
                        aria-hidden="true"
                    >
                @endfor
            </div>
        </div>
    </section>

    {{-- =========================
         FEATURES
    ========================== --}}
    <section class="mb-4 hero-features">
        <div class="text-center mb-4">
            <h2 class="card-title">Everything You Need — Nothing You Don’t</h2>
            <p class="mb-0 max-w-700 mx-auto text-white" style="color: #ffffff;">
                SharpFleet focuses on the day-to-day realities of running vehicles and drivers,
                without forcing expensive hardware or rigid workflows on your business.
            </p>
        </div>

        <div class="grid grid-3">
            <div class="card">
                <div class="card-header">
                    <h3>🚗 Trip & Logbook Tracking</h3>
                </div>
                <p>
                    Record trips, purposes, and distances in a way that suits your business.
                    Perfect for compliance, tax, and internal reporting — without GPS surveillance.
                </p>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>👥 Driver Management</h3>
                </div>
                <p>
                    Add drivers, assign vehicles, and control what information is required.
                    Your business sets the rules — SharpFleet simply keeps it organised.
                </p>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>🛠 Vehicle Records</h3>
                </div>
                <p>
                    Track registrations, servicing, maintenance notes, and key dates.
                    Get reminders before things expire — not after you get fined.
                </p>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>🦺 Safety Checks</h3>
                </div>
                <p>
                    Create and record vehicle safety checks that match your operations.
                    Simple checklists that help protect drivers and demonstrate due diligence.
                </p>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>🏢 Client & Job Linking</h3>
                </div>
                <p>
                    Optionally tie trips to clients or jobs — ideal for sole traders,
                    contractors, and service businesses needing cleaner records.
                </p>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>📱 Works Anywhere</h3>
                </div>
                <p>
                    SharpFleet works on phones, tablets, and desktops.
                    No special devices. No installs. Just log in and get on with work.
                </p>
            </div>
        </div>
    </section>

    {{-- =========================
         CTA
    ========================== --}}
    <div class="hero">
        <h2>Built for Businesses Like Yours</h2>
        <p>
            SharpFleet is designed for real-world operations — tradies, service companies,
            small fleets, and growing teams who want clarity without complexity.
        </p>
        <a href="/app/sharpfleet/admin/register" class="btn">
            Start Your Free Trial
        </a>
    </div>  

@endsection
