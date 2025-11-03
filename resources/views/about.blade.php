<!-- 
  Page: about.blade.php
  Version: v3.3 (Story Layout using shared content stylesheet)
  Last updated: 03 Nov 2025 by Max (ChatGPT)
  Description: About SharpLync – story-based layout with CPU background and responsive image wrap.
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

  <!-- ✅ Intro section -->
  <div class="hero-text">
    <h1>About <span class="highlight">SharpLync</span></h1>
    <p>From the Granite Belt to the Cloud — we’re bridging the gap between people and technology with old school support and modern results.</p>
  </div>

  <!-- ✅ Story section -->
  <div class="content-page fade-section">
    <img src="{{ asset('images/about-team.jpg') }}" alt="SharpLync Team" class="content-image left">
    <p>
      Born in the heart of the Granite Belt, <strong>SharpLync</strong> was founded with one simple goal — to make technology <em>human again</em>. 
      What began as a small IT initiative focused on local businesses and schools has evolved into a trusted technology partner serving 
      Warwick, Stanthorpe, Tenterfield, and beyond.
    </p>

    <p>
      We understand that technology can feel overwhelming — and that’s why SharpLync exists. 
      We bring a personal, local-first approach to modern IT. Whether it’s helping a small business 
      get connected securely, supporting a local school’s BYOD program, or guiding individuals through 
      digital transformation, we do it with genuine care and old school integrity.
    </p>

    <img src="{{ asset('images/about-laptop.jpg') }}" alt="SharpLync in Action" class="content-image right">
    <p>
      At SharpLync, we believe in the power of people backed by technology — not the other way around. 
      Our founder built this company on the principle of <strong>Old School Support, Modern Results</strong>. 
      That means real conversations, real help, and real accountability. 
      Because at the end of the day, technology should work for you — not make you work harder.
    </p>

    <p>
      From the Granite Belt to the Cloud, we’re building a reputation for dependable service, 
      transparent communication, and innovative solutions that make a difference. 
      Our mission is simple: keep our clients connected, protected, and supported — 
      with a handshake, not just a ticket number.
    </p>
  </div>

  <!-- ✅ Closing message -->
  <div class="hero-text fade-section" style="margin-top: 40px;">
    <h2 class="highlight">Go Big. Go SharpLync.</h2>
    <p>We’re here to bring people and technology together — with a handshake, not just a ticket number.</p>
    <a href="{{ url('/contact') }}" class="learn-more">Get in Touch →</a>
  </div>
</section>
@endsection