@extends('layouts.test-layout')

@section('title', 'SharpFleet - Advanced Fleet Management')

@section('content')
<link rel="stylesheet" href="{{ asset('css/sharpfleet-test.css') }}">

<div class="hero">
    <div class="hero-content">
        <h1>Revolutionize Your Fleet Management</h1>
        <p>Streamline operations, enhance safety, and boost efficiency with SharpFleet's comprehensive fleet management solution.</p>
        <a href="/app/sharpfleet/test-home" class="btn-primary">Get Started</a>
    </div>
</div>

<div class="features">
    <h2>Powerful Features for Modern Fleet Operations</h2>
    <div class="feature-grid">
        <div class="feature-card">
            <div class="feature-icon">📍</div>
            <h3>Real-Time Tracking</h3>
            <p>Monitor vehicle locations, routes, and performance in real-time. Get instant alerts for maintenance needs and optimize your fleet's productivity.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">👥</div>
            <h3>Driver Management</h3>
            <p>Manage driver schedules, track hours, and ensure compliance with regulations. Improve safety with driver behavior monitoring and training tools.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🔧</div>
            <h3>Maintenance & Safety</h3>
            <p>Schedule preventive maintenance, track vehicle health, and conduct safety inspections. Reduce downtime and extend vehicle lifespan.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📊</div>
            <h3>Advanced Analytics</h3>
            <p>Gain insights into fleet performance with comprehensive reporting and analytics. Make data-driven decisions to optimize costs and efficiency.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">⛽</div>
            <h3>Fuel Management</h3>
            <p>Track fuel consumption, monitor efficiency, and identify cost-saving opportunities. Reduce fuel expenses and environmental impact.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📱</div>
            <h3>Mobile Access</h3>
            <p>Access fleet data on-the-go with our mobile app. Drivers and managers can stay connected and productive from anywhere.</p>
        </div>
    </div>
</div>

<div class="stats-section">
    <div class="stats-grid">
        <div class="stat-item">
            <h3>500+</h3>
            <p>Active Fleets</p>
        </div>
        <div class="stat-item">
            <h3>10K+</h3>
            <p>Vehicles Tracked</p>
        </div>
        <div class="stat-item">
            <h3>95%</h3>
            <p>Uptime Guarantee</p>
        </div>
        <div class="stat-item">
            <h3>24/7</h3>
            <p>Support Available</p>
        </div>
    </div>
</div>

<div class="cta">
    <div class="cta-content">
        <h2>Ready to Transform Your Fleet?</h2>
        <p>Join thousands of organizations already using SharpFleet to streamline their operations.</p>
        <a href="/app/sharpfleet/test-home" class="btn-primary">Start Your Free Trial</a>
    </div>
</div>
@endsection