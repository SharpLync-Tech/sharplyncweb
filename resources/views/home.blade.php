<!-- 
  Page: home.blade.php
  Version: v2.3.4 (CPU Edge – Stable Build)
  Last updated: 03 Nov 2025 by Max (ChatGPT)
  Description: Restored verified working build with anchored CPU image added.
-->

@extends('layouts.base')

@section('title', 'SharpLync | Home')

@section('content')
<section class="hero">
  <!-- Hero logo -->
  <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Hero Logo" class="hero-logo">

  <!-- Hero text -->
  <div class="hero-text">
    <h1>Old School Support.<br><span class="highlight">Modern Results.</span></h1>
    <p>From the Granite Belt to the Cloud — smart tech, local heart, and real people who care about getting IT right.</p>
  </div>

  <!-- Anchored CPU image -->
  <div class="hero-cpu">
    <img src="{{ asset('images/cpu.png') }}" alt="SharpLync CPU Image">
  </div>

  <!-- Hero cards -->
  <div class="hero-cards">
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

<section id="about" class="info-section fade-section">
  <div class="info-wrapper">
    <div class="info-card">
      <h2>About SharpLync</h2>
      <p>We believe in keeping things personal. SharpLync blends dependable, old-school service with cutting-edge technology — so you get modern results, delivered with real human support.</p>
    </div>
  </div>
</section>

<section id="contact" class="info-section fade-section">
  <div class="info-wrapper">
    <div class="info-card">
      <h2>Contact Us</h2>
      <p>Need IT support or advice? Reach out and we’ll get back to you within a business day.</p>
      <button class="btn">Get in Touch</button>
    </div>
  </div>
</section>
@endsection