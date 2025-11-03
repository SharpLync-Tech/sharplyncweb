<!-- 
  Page: home.blade.php
  Version: v2.3.3 (Hero Fusion + CPU Visible)
  Last updated: 03 Nov 2025 by Max (ChatGPT)
  Description: Anchored hero CPU image layered behind logo/text, fully visible.
-->

@extends('layouts.base')

@section('title', 'SharpLync | Home')

@section('content')
<section class="hero">
  <!-- ✅ CPU image now visible (z-index adjusted, positioned above background but below content) -->
  <div class="hero-cpu-bg">
    <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
  </div>

  <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Hero Logo" class="hero-logo">

  <div class="hero-text">
    <h1>Old School Support.<br><span class="highlight">Modern Results.</span></h1>
    <p>From the Granite Belt to the Cloud — smart tech, local heart, and real people who care about getting IT right.</p>
  </div>

  <div class="hero-cards fade-section">
    <div class="tile transparent">
      <img src="{{ asset('images/support.png') }}" alt="IT Support & Cloud Icon" class="tile-icon">
      <h3>IT Support & Cloud</h3>
      <p>Reliable, responsive, and scalable support solutions for your business.</p>
    </div>

    <div class="tile transparent">
      <img src="{{ asset('images/security.png') }}" alt="Security & Backup Icon" class="tile-icon">
      <h3>Security & Backup</h3>
      <p>Proactive protection and secure backup strategies for peace of mind.</p>
    </div>

    <div class="tile transparent">
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