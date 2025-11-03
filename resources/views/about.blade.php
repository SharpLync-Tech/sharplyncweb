<!-- 
  Page: about.blade.php
  Version: v4.5 (Transparency Fix)
  Last updated: 04 Nov 2025 by Jannie & Max
  Description: Fixed solid card background overriding transparent tile class.
-->

@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/content-pages.css') }}">
@endpush

@section('title', 'SharpLync | About')

@section('content')
<section class="hero about-hero">
  <div class="hero-text">
    <h1>About <span class="highlight">SharpLync</span></h1>
    <p>From the Granite Belt to the Cloud — bridging the gap between people and technology with old school support and modern results.</p>
  </div>

  <div class="hero-cards fade-section">
    <!-- ✅ Removed .tile.transparent -->
    <div class="about-card">
      <h3>Our Story</h3>
      <p>
        Born in the heart of the Granite Belt, <strong>SharpLync</strong> was founded with one simple goal — 
        to make technology <em>human again</em>. What began as a local IT initiative has grown into a trusted regional partner 
        supporting Warwick, Stanthorpe, Tenterfield and beyond.
      </p>
      <p>
        We believe in community, reliability, and service that still means something. 
        Our promise is simple — real support, from real people, who genuinely care about helping others succeed. 
        From the bush to the cloud, SharpLync continues to build connections that matter.
      </p>
    </div>
  </div>
</section>
@endsection