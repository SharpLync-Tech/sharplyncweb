<!-- 
  Page: about.blade.php
  Version: v3.4 (Two-column story layout)
  Last updated: 03 Nov 2025 by Max (ChatGPT)
  Description: About SharpLync – structured layout with text column and CPU image column for balanced design.
-->

@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/content-pages.css') }}">
@endpush

@section('title', 'SharpLync | About')

@section('content')
<section class="hero">
  <!-- ✅ CPU background -->
  <div class="hero-cpu-bg">
    <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
  </div>

  <!-- ✅ Intro -->
  <div class="hero-text">
    <h1>About <span class="highlight">SharpLync</span></h1>
    <p>From the Granite Belt to the Cloud — we’re bridging the gap between people and technology with old school support and modern results.</p>
  </div>

  <!-- ✅ Two-column layout -->
  <div class="about-flex fade-section">
    <!-- Left content -->
    <div class="about-text">
      <h3>Our Story</h3>
      <p>
        Born in the heart of the Granite Belt, <strong>SharpLync</strong> was founded with one simple goal — to make technology <em>human again</em>. 
        What began as a small IT initiative focused on local businesses and schools has evolved into a trusted regional technology partner 
        serving Warwick, Stanthorpe, Tenterfield, and beyond.
      </p>

      <p>
        We understand that technology can feel overwhelming — and that’s why SharpLync exists. 
        We bring a personal, local-first approach to modern IT. Whether it’s helping a small business 
        get connected securely, supporting a local school’s BYOD program, or guiding individuals through 
        digital transformation, we do it with genuine care and old school integrity.
      </p>

      <p>
        At SharpLync, we believe in the power of people backed by technology — not the other way around. 
        Our founder built this company on the principle of <strong>Old School Support, Modern Results</strong>. 
        That means real conversations, real help, and real accountability. 
        Because at the end of the day, technology should work for you — not make you work harder.
      </p>
    </div>

    <!-- Right image -->
    <div class="about-image-container">
      <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Illustration" class="about-cpu-image">
    </div>
  </div>

  <!-- ✅ Closing -->
  <div class="hero-text fade-section" style="margin-top: 60px;">
    <h2 class="highlight">Go Big. Go SharpLync.</h2>
    <p>We’re here to bring people and technology together — with a handshake, not just a ticket number.</p>
    <a href="{{ url('/contact') }}" class="learn-more">Get in Touch →</a>
  </div>
</section>
@endsection