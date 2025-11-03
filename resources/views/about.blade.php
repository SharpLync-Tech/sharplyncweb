<!-- 
  Page: about.blade.php
  Version: v3.0 (Base Page Template)
  Last updated: 03 Nov 2025 by Max (ChatGPT)
  Description: Core layout for SharpLync pages. Includes CPU background, header, footer, and consistent styling.
-->

@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/content-pages.css') }}">
@endpush

@section('title', 'SharpLync | About')

@section('content')
<section class="hero">
  <!-- ✅ CPU image visible behind content -->
  <div class="hero-cpu-bg">
    <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
  </div>

  <!-- ✅ Page heading/logo area (replace or remove as needed) -->
  <div class="hero-text">
    <h1>Abount SharpLync</h1>
    <p>Intro text or short description can go here. Replace with page-specific content.</p>
  </div>

  <!-- ✅ Page content/cards area -->
  <div class="hero-cards fade-section">
    <div class="tile transparent">
      <img src="{{ asset('images/sample-icon.png') }}" alt="Feature Icon" class="tile-icon">
      <h3>Section Heading</h3>
      <p>
        Add your page content here. This layout is designed to stay consistent with the SharpLync brand look.
      </p>
      <!-- <a href="#" class="learn-more">Learn more →</a> -->
    </div>    
  </div>
</section>
@endsection
