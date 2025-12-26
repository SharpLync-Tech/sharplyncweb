@extends('layouts.sharpfleet')

@section('title', 'SharpFleet – Simple Fleet Management for Real Businesses')

@section('sharpfleet-content')
<div class="hero">
    <h1>Fleet Management, Without the Headaches</h1>
    <p>
        SharpFleet is a simple, flexible fleet management platform built for the sole trader, small and
        medium businesses. No tracking hardware. No micromanagement.
        Just the tools you need to stay organised, compliant, and in control.
    </p>
    <a href="/app/sharpfleet/admin/register" class="btn">Get Started</a>
    <p class="mt-2 small text-muted">
        Already have an account? <a href="/app/sharpfleet/login" class="text-primary">Sign in here</a>
    </p>
</div>

<section class="mb-4">
    <div class="text-center mb-4">
        <h2 class="card-title">Everything You Need — Nothing You Don’t</h2>
        <p class="mb-0 max-w-700 mx-auto text-muted">
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

<div class="hero">
    <h2>Built for Businesses Like Yours</h2>
    <p>
        SharpFleet is designed for real-world operations — tradies, service companies,
        small fleets, and growing teams who want clarity without complexity.
    </p>
    <a href="/app/sharpfleet/admin/register" class="btn">Start Your Free Trial</a>
</div>
@endsection
