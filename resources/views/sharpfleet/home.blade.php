@extends('layouts.sharpfleet')

@section('title', 'SharpFleet - Advanced Fleet Management')

@section('sharpfleet-content')
<div class="hero">
    <h1>Revolutionize Your Fleet Management</h1>
    <p>Streamline operations, enhance safety, and boost efficiency with SharpFleet's comprehensive fleet management solution.</p>
    <a href="/app/sharpfleet/login" class="btn">Get Started</a>
</div>

<section class="mb-4">
    <div class="text-center mb-4">
        <h2 class="card-title">Powerful Features for Modern Fleet Operations</h2>
    </div>
    <div class="grid grid-3">
        <div class="card">
            <div class="card-header">
                <h3>📍 Real-Time Tracking</h3>
            </div>
            <p>Monitor vehicle locations, routes, and performance in real-time. Get instant alerts for maintenance needs and optimize your fleet's productivity.</p>
        </div>
        <div class="card">
            <div class="card-header">
                <h3>👥 Driver Management</h3>
            </div>
            <p>Manage driver schedules, track hours, and ensure compliance with regulations. Improve safety with driver behavior monitoring and training tools.</p>
        </div>
        <div class="card">
            <div class="card-header">
                <h3>🔧 Maintenance & Safety</h3>
            </div>
            <p>Schedule preventive maintenance, track vehicle health, and conduct safety inspections. Reduce downtime and extend vehicle lifespan.</p>
        </div>
        <div class="card">
            <div class="card-header">
                <h3>📊 Advanced Analytics</h3>
            </div>
            <p>Gain insights into fleet performance with comprehensive reporting and analytics. Make data-driven decisions to optimize costs and efficiency.</p>
        </div>
        <div class="card">
            <div class="card-header">
                <h3>⛽ Fuel Management</h3>
            </div>
            <p>Track fuel consumption, monitor efficiency, and identify cost-saving opportunities. Reduce fuel expenses and environmental impact.</p>
        </div>
        <div class="card">
            <div class="card-header">
                <h3>📱 Mobile Access</h3>
            </div>
            <p>Access fleet data on-the-go with our mobile app. Drivers and managers can stay connected and productive from anywhere.</p>
        </div>
    </div>
</section>

<div class="hero">
    <h2>Ready to Transform Your Fleet?</h2>
    <p>Join thousands of organizations already using SharpFleet to streamline their operations.</p>
    <a href="/app/sharpfleet/login" class="btn">Start Your Free Trial</a>
</div>
@endsection
