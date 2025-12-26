@extends('layouts.sharpfleet')

@section('title', 'SharpFleet - Advanced Fleet Management')

@section('sharpfleet-content')
<style>
    .hero {
        background: linear-gradient(135deg, #0A2A4D 0%, #2CBFAE 100%);
        color: white;
        padding: 80px 20px;
        text-align: center;
    }
    .hero h1 {
        font-size: 48px;
        margin-bottom: 20px;
        font-weight: 700;
    }
    .hero p {
        font-size: 20px;
        margin-bottom: 40px;
        opacity: 0.9;
    }
    .btn-primary {
        background: #2CBFAE;
        color: white;
        padding: 16px 32px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 18px;
        display: inline-block;
        transition: background 0.3s;
    }
    .btn-primary:hover {
        background: #25a99a;
    }
    .features {
        padding: 80px 20px;
        background: #f8f9fa;
    }
    .features h2 {
        text-align: center;
        font-size: 36px;
        margin-bottom: 60px;
        color: #0A2A4D;
    }
    .feature-grid {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 40px;
    }
    .feature-card {
        background: white;
        padding: 40px 30px;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        text-align: center;
    }
    .feature-card h3 {
        font-size: 24px;
        margin-bottom: 20px;
        color: #0A2A4D;
    }
    .feature-card p {
        color: #666;
        line-height: 1.6;
    }
    .cta {
        padding: 80px 20px;
        text-align: center;
        background: #0A2A4D;
        color: white;
    }
    .cta h2 {
        font-size: 36px;
        margin-bottom: 20px;
    }
    .cta p {
        font-size: 20px;
        margin-bottom: 40px;
        opacity: 0.9;
    }
</style>

<div class="hero">
    <h1>Revolutionize Your Fleet Management</h1>
    <p>Streamline operations, enhance safety, and boost efficiency with SharpFleet's comprehensive fleet management solution.</p>
    <a href="/app/sharpfleet/login" class="btn-primary">Get Started</a>
</div>

<div class="features">
    <h2>Powerful Features for Modern Fleet Operations</h2>
    <div class="feature-grid">
        <div class="feature-card">
            <h3>Real-Time Tracking</h3>
            <p>Monitor vehicle locations, routes, and performance in real-time. Get instant alerts for maintenance needs and optimize your fleet's productivity.</p>
        </div>
        <div class="feature-card">
            <h3>Driver Management</h3>
            <p>Manage driver schedules, track hours, and ensure compliance with regulations. Improve safety with driver behavior monitoring and training tools.</p>
        </div>
        <div class="feature-card">
            <h3>Maintenance & Safety</h3>
            <p>Schedule preventive maintenance, track vehicle health, and conduct safety inspections. Reduce downtime and extend vehicle lifespan.</p>
        </div>
        <div class="feature-card">
            <h3>Advanced Analytics</h3>
            <p>Gain insights into fleet performance with comprehensive reporting and analytics. Make data-driven decisions to optimize costs and efficiency.</p>
        </div>
        <div class="feature-card">
            <h3>Fuel Management</h3>
            <p>Track fuel consumption, monitor efficiency, and identify cost-saving opportunities. Reduce fuel expenses and environmental impact.</p>
        </div>
        <div class="feature-card">
            <h3>Mobile Access</h3>
            <p>Access fleet data on-the-go with our mobile app. Drivers and managers can stay connected and productive from anywhere.</p>
        </div>
    </div>
</div>

<div class="cta">
    <h2>Ready to Transform Your Fleet?</h2>
    <p>Join thousands of organizations already using SharpFleet to streamline their operations.</p>
    <a href="/app/sharpfleet/login" class="btn-primary">Start Your Free Trial</a>
</div>
@endsection