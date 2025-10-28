@extends('layouts.app')

@section('title', 'SharpLync | Empowering Your Digital Journey')

@section('content')
<div class="page-content">

    <!-- ===== HERO SECTION ===== -->
    <section class="hero">
        <div class="hero-text">
            <h1>Empowering Your <span>Digital Journey</span></h1>
            <p>Tailored IT solutions for future-ready businesses.</p>
            <button class="btn-accent">Learn More</button>
        </div>
        <div class="hero-graphic">
            <!-- Replace this placeholder once CPU image is ready -->
            <div class="cpu-placeholder">[ CPU IMAGE HERE ]</div>
        </div>
    </section>

    <!-- ===== FEATURE TILES SECTION ===== -->
    <section class="feature-tiles">
        <div class="tile">
            <div class="tile-icon">☁️</div>
            <h3 class="tile-title">Cloud Computing</h3>
            <p class="tile-text">Scalable, secure and flexible solutions.</p>
            <button class="btn-secondary">Learn More</button>
        </div>

        <div class="tile">
            <div class="tile-icon">💼</div>
            <h3 class="tile-title">Managed IT Services</h3>
            <p class="tile-text">Predicting your needs, protecting your data.</p>
            <button class="btn-secondary">Learn More</button>
        </div>

        <div class="tile">
            <div class="tile-icon">🌐</div>
            <h3 class="tile-title">Network Solutions</h3>
            <p class="tile-text">Unlock high-speed performance and uptime.</p>
            <button class="btn-secondary">Learn More</button>
        </div>

        <div class="tile">
            <div class="tile-icon">📱</div>
            <h3 class="tile-title">AI & Mobile Dev</h3>
            <p class="tile-text">Custom applications for smart operations.</p>
            <button class="btn-secondary">Learn More</button>
        </div>
    </section>

</div>
@endsection