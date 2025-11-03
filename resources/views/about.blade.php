<!-- 
  Page: about.blade.php
  Version: v4.1 (Template Card Layout with Position Notes)
  Last updated: 03 Nov 2025 by Max (ChatGPT)
  Description: Restores original centered card design with clear controls in stylesheet.
-->

@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/content-pages.css') }}">
@endpush

@section('title', 'SharpLync | About')

@section('content')
<section class="hero about-hero">
   <!-- ✅ Hero heading -->
  <div class="hero-text">
    <h1>About <span class="highlight">SharpLync</span></h1>
    <p>From the Granite Belt to the Cloud — bridging the gap between people and technology with old school support and modern results.</p>
  </div>

  <!-- ✅ Main centered card -->
  <div class="hero-cards fade-section">
    <div class="tile transparent">
      <!-- <img src="{{ asset('images/sample-icon.png') }}" alt="Feature Icon" class="tile-icon"> -->
      <h3>Our Story</h3>
      <p>
        Born in the heart of the Granite Belt, SharpLync was founded with one simple goal — to make technology human again. 
        What began as a local IT initiative has grown into a trusted regional partner supporting Warwick, Stanthorpe, Tenterfield and beyond.
      </p>
    </div>    
  </div>
</section>
@endsection