@extends('layouts.sharpfleet')

@section('title', 'SharpFleet - Advanced Fleet Management')

@section('sharpfleet-content')

<div class="hero">
    <h1>Revolutionize Your Fleet Management</h1>
    <p>Streamline operations, enhance safety, and boost efficiency with SharpFleet’s fleet management tools.</p>
    <a href="/app/sharpfleet/login" class="btn">Get Started</a>
</div>

<section class="mb-4">
    <div class="text-center mb-4">
        <h2 class="card-title">Powerful Features for Modern Fleet Operations</h2>
        <p class="mb-0 max-w-700 mx-auto text-muted">
            Built for teams who want clarity without complexity.
        </p>
    </div>

    <div class="grid grid-3">
        <div class="card">
            <div class="card-header"><h3>📍 Real-Time Tracking</h3></div>
            <p>Monitor vehicle locations, routes, and performance in real-time and optimize productivity.</p>
        </div>
        <div class="card">
            <div class="card-header"><h3>👥 Driver Management</h3></div>
            <p>Manage driver schedules, track hours, and improve compliance with clear workflows.</p>
        </div>
        <div class="card">
            <div class="card-header"><h3>🔧 Maintenance & Safety</h3></div>
            <p>Track servicing and safety tasks to reduce downtime and support due diligence.</p>
        </div>
        <div class="card">
            <div class="card-header"><h3>📊 Analytics</h3></div>
            <p>Get reporting insights to make better operational decisions.</p>
        </div>
        <div class="card">
            <div class="card-header"><h3>⛽ Fuel Management</h3></div>
            <p>Track fuel-related records to help identify cost-saving opportunities.</p>
        </div>
        <div class="card">
            <div class="card-header"><h3>📱 Mobile Access</h3></div>
            <p>Use SharpFleet on phones, tablets, and desktops — no installs.</p>
        </div>
    </div>
</section>

<div class="grid grid-4 mb-4">
    <div class="stats-card">
        <div class="stats-number">500+</div>
        <div class="stats-label">Active Fleets</div>
    </div>
    <div class="stats-card">
        <div class="stats-number">10K+</div>
        <div class="stats-label">Vehicles Tracked</div>
    </div>
    <div class="stats-card">
        <div class="stats-number">95%</div>
        <div class="stats-label">Uptime Target</div>
    </div>
    <div class="stats-card">
        <div class="stats-number">24/7</div>
        <div class="stats-label">Support</div>
    </div>
</div>

<div class="hero">
    <h2>Ready to Transform Your Fleet?</h2>
    <p>Join organisations using SharpFleet to streamline their operations.</p>
    <a href="/app/sharpfleet/admin/register" class="btn">Start Your Free Trial</a>
</div>

@endsection