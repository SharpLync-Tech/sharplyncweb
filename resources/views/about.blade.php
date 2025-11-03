<!-- 
  Page: about.blade.php
  Version: v3.1 (About Page)
  Last updated: 03 Nov 2025 by Max (ChatGPT)
  Description: About SharpLync – our story, values, and vision brought to life with clean layout and CPU background.
-->

@extends('layouts.base')

@section('title', 'SharpLync | About')

@section('content')
<section class="hero">
  <!-- ✅ CPU background -->
  <div class="hero-cpu-bg">
    <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
  </div>

  <!-- ✅ Intro section -->
  <div class="hero-text">
    <h1>About <span class="highlight">SharpLync</span></h1>
    <p>From the Granite Belt to the Cloud — we’re bridging the gap between people and technology with old school support and modern results.</p>
  </div>

  <!-- ✅ Mission / Story Cards -->
  <div class="hero-cards fade-section">

    <div class="tile transparent">
      <img src="{{ asset('images/icons/mission.png') }}" alt="Mission Icon" class="tile-icon">
      <h3>Our Story</h3>
      <p>
        Born in the heart of the Granite Belt, SharpLync was created with one goal — to make technology simple, secure, and human again. 
        What started as a local IT initiative has grown into a full-service tech partner serving Warwick, Stanthorpe, Tenterfield and beyond.
      </p>
    </div>

    <div class="tile transparent">
      <img src="{{ asset('images/icons/values.png') }}" alt="Values Icon" class="tile-icon">
      <h3>Our Values</h3>
      <p>
        We believe in reliability, transparency, and the kind of service that used to mean something. 
        Our promise: local expertise, backed by enterprise-grade technology and genuine care for our clients.
      </p>
    </div>

    <div class="tile transparent">
      <img src="{{ asset('images/icons/vision.png') }}" alt="Vision Icon" class="tile-icon">
      <h3>Our Vision</h3>
      <p>
        To be the trusted link between business and technology — a name synonymous with support, innovation, and integrity. 
        We’re building solutions that empower individuals, schools, and businesses to thrive in a connected world.
      </p>
    </div>

  </div>

  <!-- ✅ CTA / closing message -->
  <div class="hero-text fade-section" style="margin-top: 40px;">
    <h2 class="highlight">Go Big. Go SharpLync.</h2>
    <p>We’re here to bring people and technology together — with a handshake, not just a ticket number.</p>
    <a href="{{ url('/contact') }}" class="learn-more">Get in Touch →</a>
  </div>

</section>
@endsection