@extends('layouts.base')

@section('title', 'SharpLync | IT Support & Cloud Services')

@section('content')
<!-- ==================== HERO ==================== -->
<section class="hero-section">
  <div class="hero-content">
    <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo" class="hero-logo">

    <div class="hero-text">
      <h1>
        Old School Support.<br>
        <span>Modern Results.</span>
      </h1>
      <p>From the Granite Belt to the Cloud — smart tech, local heart, and real people who care about getting IT right.</p>
      <div class="hero-buttons">
        <a href="#contact" class="btn btn-primary">Contact Us</a>
        <a href="#services" class="btn btn-secondary">Learn More</a>
      </div>
    </div>
  </div>
</section>

<!-- ==================== SERVICES ==================== -->
<section id="services" class="services-section">
  <h2>What We Do Best</h2>
  <div class="service-cards">
    <div class="service-card">
      <img src="{{ asset('images/icon-support.svg') }}" alt="Support Icon">
      <h3>IT Support & Cloud</h3>
      <p>Reliable, responsive, and scalable support solutions for your business.</p>
    </div>

    <div class="service-card">
      <img src="{{ asset('images/icon-security.svg') }}" alt="Security Icon">
      <h3>Security & Backup</h3>
      <p>Proactive protection and secure backup strategies for peace of mind.</p>
    </div>

    <div class="service-card">
      <img src="{{ asset('images/icon-infra.svg') }}" alt="Infrastructure Icon">
      <h3>Infrastructure Design</h3>
      <p>Tailored networks built for reliability and long-term performance.</p>
    </div>
  </div>
</section>
@endsection