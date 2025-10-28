@extends('layouts.base')

@section('title', 'SharpLync | Home')

@section('content')
<section class="hero">
    <div class="hero-text">
        <h1 class="fade-in">Old School Support.<br><span>Modern Results.</span></h1>
        <p class="fade-in-delay">Reliable IT solutions designed for modern businesses — delivered with real human support.</p>
        <p class="fade-in-delay tagline-sub">Proudly supporting regional Queensland businesses with dependable, local expertise.</p>
        <div class="hero-buttons fade-in-btn">
            <button class="btn-accent" onclick="document.getElementById('contact').scrollIntoView({ behavior: 'smooth' });">Contact Us</button>
            <button class="btn" onclick="document.getElementById('services').scrollIntoView({ behavior: 'smooth' });">Learn More</button>
        </div>
    </div>
    <div class="hero-image">
        <div class="cpu-glow"></div>
        <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync Hero Image">
    </div>
</section>

<section id="services" class="tiles-section">
    <div class="tile fade-up">
        <h3>IT Support & Cloud</h3>
        <p>Comprehensive, scalable solutions for your business — on-site or remote.</p>
    </div>
    <div class="tile fade-up">
        <h3>Security & Backup</h3>
        <p>Stay safe with proactive protection and encrypted backup strategies.</p>
    </div>
    <div class="tile fade-up">
        <h3>Infrastructure Design</h3>
        <p>Tailored networks built for reliability and long-term performance.</p>
    </div>
    <div class="tile fade-up">
        <h3>SharpLync SafeCheck</h3>
        <p>Free AI-powered scam and phishing detection to keep your business secure.</p>
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