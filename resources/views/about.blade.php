<!-- 
  Page: about.blade.php
  Version: v4.0 (Stable Left Glowing Card Layout)
  Last updated: 03 Nov 2025 by Max (ChatGPT)
  Description: Correctly styled About page with glowing left-side card and annotated controls.
-->

@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/content-pages.css') }}">
@endpush

@section('title', 'SharpLync | About')

@section('content')
<section class="hero about-hero">
  <!-- ✅ CPU background (from base) -->
  <div class="hero-cpu-bg">
    <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
  </div>

  <!-- ✅ Page header -->
  <div class="hero-text">
    <h1>About <span class="highlight">SharpLync</span></h1>
    <p>From the Granite Belt to the Cloud — bridging the gap between people and technology with old school support and modern results.</p>
  </div>

  <!-- ✅ Glowing Card -->
  <div class="hero-cards fade-section">
    <div class="about-card">
      <h3>Our Story</h3>
      <p>
        Born in the heart of the Granite Belt, SharpLync was founded with one simple goal — to make technology human again. 
        What began as a local IT initiative has grown into a trusted regional partner supporting Warwick, Stanthorpe, Tenterfield and beyond.
      </p>
    </div>
  </div>
</section>
@endsection