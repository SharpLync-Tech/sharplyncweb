<!-- 
  Page: home.blade.php
  Version: v1.7
  Last updated: 29 Oct 2025 by Max (ChatGPT)
  Description: Converted About and Contact sections into responsive cards with brand styling.
-->

@extends('layouts.base')

@section('title', 'SharpLync | Home')

@section('content')
  <section class="hero">
    <div class="hero-text">
      <h1>Old School Support.<br><span>Modern Results.</span></h1>
      <p>Reliable IT solutions designed for modern businesses — delivered by real human experts who get it done right, the first time.</p>
      <div class="hero-buttons">
        <button class="btn-accent">Contact Us</button>
        <button class="btn">Learn More</button>
      </div>
      <div class="trust-bar">
        <p>20+ Years Experience&nbsp;&nbsp;|&nbsp;&nbsp;99.9% Uptime</p>
      </div>
    </div>
    <div class="hero-image">
      <img src="{{ asset('images/cpu.png') }}" alt="CPU Chip Graphic">
    </div>
  </section>

  <section class="tiles-section">
    <h2>What We Do Best</h2>
    <div class="tiles-wrapper">
      <div class="tile">
        <img src="{{ asset('images/support.png') }}" alt="IT Support Icon" class="tile-icon">
        <h3>IT Support &amp; Cloud</h3>
        <p>Reliable, responsive, and scalable support solutions for your business.</p>
      </div>
      <div class="tile">
        <img src="{{ asset('images/security.png') }}" alt="Security Icon" class="tile-icon">
        <h3>Security &amp; Backup</h3>
        <p>Proactive protection and secure backup strategies for peace of mind.</p>
      </div>
      <div class="tile">
        <img src="{{ asset('images/infrastructure.png') }}" alt="Infrastructure Icon" class="tile-icon">
        <h3>Infrastructure Design</h3>
        <p>Tailored networks built for reliability and long-term performance.</p>
      </div>
    </div>
  </section>

  <!-- === New About & Contact Cards === -->
  <section class="info-section">
    <div class="info-wrapper">
      <div class="info-card">
        <h3>About SharpLync</h3>
        <p>We believe in keeping things personal. SharpLync blends dependable, old-school service with cutting-edge technology — so you get modern results, delivered with real human support.</p>
      </div>
      <div class="info-card">
        <h3>Contact Us</h3>
        <p>Need IT support or advice? Reach out and we’ll get back to you within a business day.</p>
        <button class="btn">Get in Touch</button>
      </div>
    </div>
  </section>
@endsection