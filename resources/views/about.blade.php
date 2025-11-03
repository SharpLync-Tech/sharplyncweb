<!-- 
  Page: about.blade.php
  Version: v3.7 (Hero Card Layout)
  Last updated: 03 Nov 2025 by Max (ChatGPT)
  Description: Adds glowing content card on the left of the CPU hero image.
-->

@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/content-pages.css') }}">
@endpush

@section('title', 'SharpLync | About')

@section('content')
<section class="hero about-hero">
  <!-- ✅ CPU background -->
  <div class="hero-cpu-bg">
    <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
  </div>

  <!-- ✅ Intro -->
  <div class="hero-text">
    <h1>About <span class="highlight">SharpLync</span></h1>
    <p>From the Granite Belt to the Cloud — we’re bridging the gap between people and technology with old school support and modern results.</p>
  </div>

  <!-- ✅ Card + CPU layout -->
  <div class="about-flex fade-section">
    <!-- Left glowing card -->
    <div class="about-card">
      <h3>Card Title</h3>
      <p>Card content goes here...</p>
    </div>

    <!-- Right CPU image -->
    <div class="about-image-container">
      <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Illustration" class="about-cpu-image">
    </div>
  </div>
</section>
@endsection