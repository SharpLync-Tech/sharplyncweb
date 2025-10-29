<!-- 
  Page: home.blade.php
  Version: v1.8
  Last updated: 29 Oct 2025 by Max (ChatGPT)
  Description: Removed trust bar, added button spacing, reduced hero vertical padding for better balance.
-->

@extends('layouts.base')

@section('title', 'SharpLync | Home')

@section('content')
<section class="hero">
    <div class="hero-text">
        <h1>Old School Support.<br><span>Modern Results.</span></h1>
        <p>Reliable IT solutions designed for modern businesses — delivered by real human experts who get it done right, the first time.</p>
        <div class="hero-buttons">
            <button class="btn-accent" onclick="document.getElementById('contact').scrollIntoView({ behavior: 'smooth' });">Contact Us</button>
            <button class="btn" onclick="document.getElementById('services').scrollIntoView({ behavior: 'smooth' });">Learn More</button>
        </div>
    </div>

    <div class="hero-image">
        <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync Hero Image">
    </div>
</section>

<section id="services" class="tiles-section">
    <h2>What We Do Best</h2>
    <div class="tiles-wrapper">
        <div class="tile">
            <img src="{{ asset('images/support.png') }}" alt="IT Support & Cloud Icon" class="tile-icon">
            <h3>IT Support & Cloud</h3>
            <p>Reliable, responsive, and scalable support solutions for your business.</p>
        </div>
        <div class="tile">
            <img src="{{ asset('images/security.png') }}" alt="Security & Backup Icon" class="tile-icon">
            <h3>Security & Backup</h3>
            <p>Proactive protection and secure backup strategies for peace of mind.</p>
        </div>
        <div class="tile">
            <img src="{{ asset('images/infrastructure.png') }}" alt="Infrastructure Design Icon" class="tile-icon">
            <h3>Infrastructure Design</h3>
            <p>Tailored networks built for reliability and long-term performance.</p>
        </div>
    </div>
</section>

<!-- About area as card -->
<section id="about" class="info-section">
    <div class="info-wrapper">
        <div class="info-card">
            <h2>About SharpLync</h2>
            <p>We believe in keeping things personal. SharpLync blends dependable, old-school service with cutting-edge technology — so you get modern results, delivered with real human support.</p>
        </div>
    </div>
</section>

<!-- Contact area as card -->
<section id="contact" class="info-section">
    <div class="info-wrapper">
        <div class="info-card">
            <h2>Contact Us</h2>
            <p>Need IT support or advice? Reach out and we’ll get back to you within a business day.</p>
            <button class="btn">Get in Touch</button>
        </div>
    </div>
</section>
@endsection