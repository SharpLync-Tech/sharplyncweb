<!-- 
  Page: home.blade.php
  Version: v2.3.3 (Hero Fusion + CPU Visible)
  Last updated: 03 Nov 2025 by Max (ChatGPT)
  Description: Anchored hero CPU image layered behind logo/text, fully visible.
-->

@extends('layouts.base')

@section('title', 'SharpLync | Home')

@section('content')
<section class="hero">
  <!-- ✅ CPU image now visible (z-index adjusted, positioned above background but below content) -->
  <div class="hero-cpu-bg">
    <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Background">
  </div>

  <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Hero Logo" class="hero-logo">

  <div class="hero-text">
    <h1>Your Business,<br><span class="highlight">Secure & Connected</span></h1>
    <p>From the Granite Belt to the Cloud — smart tech, local heart, and real people who care about getting IT right.</p>
  </div>

  <div class="hero-cards fade-section">

  <div class="tile transparent">
    <img src="{{ asset('images/about.png') }}" alt="About SharpLync" class="tile-icon">
    <h3>About SharpLync</h3>
    <p>
      We’re your tech partner! Great service never goes out of style.
      Reliable people, smarter systems, and genuine care for getting IT right.
    </p>
    <a href="/about" class="learn-more">Learn more</a>
  </div>

  <div class="tile transparent">
    <img src="{{ asset('images/what_we_do.png') }}" alt="What We Do" class="tile-icon">
    <h3>What We Do</h3>
    <p>
      From cloud and security to on-site infrastructure. SharpLync keeps your business connected,
      protected, and performing at its best. Real solutions, real people, real results.
    </p>
    <a href="/services" class="learn-more">Learn more</a>
  </div>

  <div class="tile transparent">
    <img src="{{ asset('images/contact_us.png') }}" alt="Contact Us" class="tile-icon">
    <h3>Contact Us</h3>
    <p>
      Need help or advice? Reach out anytime. Proudly based in the Granite Belt —
      proudly supporting clients nationwide. Quick support, friendly faces, no fuss.
    </p>
    <a href="/contact" class="learn-more">Learn more</a>
  </div>

</div>

  </div>
</section>
@endsection