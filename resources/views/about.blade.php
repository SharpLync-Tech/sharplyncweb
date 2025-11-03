<!-- 
  Page: about.blade.php
  Version: v3.8 (Fixed Hero Card Layout)
  Last updated: 03 Nov 2025 by Max (ChatGPT)
  Description: Clean layout using base hero CPU background and a single glowing card on the left.
-->

@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/content-pages.css') }}">
@endpush

@section('title', 'SharpLync | About')

@section('content')
<section class="hero about-hero">
  <!-- ✅ CPU background already provided by base layout -->

  <!-- ✅ Hero intro text -->
  <div class="hero-text">
    <h1>About <span class="highlight">SharpLync</span></h1>
    <p>From the Granite Belt to the Cloud — we’re bridging the gap between people and technology with old school support and modern results.</p>
  </div>

  <!-- ✅ Left-side glowing card -->
  <div class="about-card fade-section">
    <h3>Our Story</h3>
    <p>
      Born in the heart of the Granite Belt, SharpLync was founded with one simple goal — 
      to make technology human again. What began as a local IT initiative has grown into 
      a trusted technology partner supporting Warwick, Stanthorpe, Tenterfield and beyond.
    </p>
  </div>
</section>
@endsection