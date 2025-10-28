<!-- 
  Page: home.blade.php
  Version: v1.0
  Description: Added trust bar and mobile responsive layout improvements.
  Last updated: 29 Oct 2025 by Max (ChatGPT)
-->

@extends('layouts.base')

@section('title', 'SharpLync | Home')

@section('content')
<section class="hero">
    <div class="hero-text">
        <h1 class="fade-in">Old School Support.<br><span>Modern Results.</span></h1>
        <p class="fade-in-delay">Reliable IT solutions designed for modern businesses — delivered by real human experts who get it done right, the first time.</p>
        <div class="hero-buttons fade-in-btn">
            <button class="btn-accent" onclick="document.getElementById('contact').scrollIntoView({ behavior: 'smooth' });">Contact Us</button>
            <button class="btn" onclick="document.getElementById('services').scrollIntoView({ behavior: 'smooth' });">Learn More</button>
        </div>
        <div class="trust-bar fade-in-delay">
            <div class="trust-item"><i class="fas fa-trophy"></i> 20+ Years Experience</div>
            <div class="trust-item"><i class="fas fa-gauge-high"></i> 99.9% Uptime</div>
        </div>
    </div>

    <div class="hero-image">
        <div class="cpu-glow"></div>
        <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync Hero Image">
    </div>
</section>

<section id="services" class="tiles-section">
    <h2>What We Do Best</h2>
    <div class="tiles-wrapper">
        <div class="tile fade-up">
            <i class="fas fa-headset"></i>
            <h3>IT Support & Cloud</h3>
            <p>Reliable, responsive, and scalable support solutions for your business.</p>
        </div>
        <div class="tile fade-up">
            <i class="fas fa-shield-alt"></i>
            <h3>Security & Backup</h3>
            <p>Proactive protection and secure backup strategies for peace of mind.</p>
        </div>
        <div class="tile fade-up">
            <i class="fas fa-network-wired"></i>
            <h3>Infrastructure Design</h3>
            <p>Tailored networks built for reliability and long-term performance.</p>
        </div>
    </div>
</section>

<section id="about" class="about-section">
    <h2>About SharpLync</h2>
    <p>We believe in keeping things personal. SharpLync blends dependable, old-school service with cutting-edge technology — so you get modern results, delivered with real human support.</p>
</section>

<section id="contact" class="contact-section">
    <h2>Contact Us</h2>
    <p>Need IT support or advice? Reach out and we’ll get back to you within a business day.</p>
    <button class="btn">Get in Touch</button>
</section>
@endsection