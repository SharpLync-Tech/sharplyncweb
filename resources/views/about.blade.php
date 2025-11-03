<!-- 
  Page: about.blade.php
  Version: v6.0 (With Testimonials)
  Last updated: 04 Nov 2025 by Jannie & Max
  Description: SharpLync content layout with solid story card and cycling testimonial section.
-->

@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/content-pages.css') }}">
@endpush

@section('title', 'SharpLync | About')

@section('content')
<section class="content-hero fade-in">

  <!-- ✅ Page heading -->
  <div class="content-header">
    <h1>About <span class="highlight">SharpLync</span></h1>
    <p>From the Granite Belt to the Cloud — bridging the gap between people and technology with old school support and modern results.</p>
  </div>

  <!-- ✅ Our Story Card -->
  <div class="content-card">
    <h3>Our Story</h3>
    <p>
      Born in the heart of the Granite Belt, <strong>SharpLync</strong> was founded with one simple goal —
      to make technology <em>human again</em>. What began as a small local IT initiative has grown into a trusted 
      regional partner supporting Warwick, Stanthorpe, Tenterfield, and beyond.
    </p>

    <p>
      We believe in community, reliability, and service that still means something. 
      Our promise is simple — real support, from real people, who genuinely care about helping others succeed. 
      From the bush to the cloud, SharpLync continues to build connections that matter.
    </p>
  </div>

  <!-- ✅ Testimonials Section -->
  <section class="testimonials-section fade-in">
    <h3>What People Say</h3>

    <div class="testimonial-container">
      <div class="testimonial active">
        <p>"Jannie is one of the most dependable and dedicated IT professionals I’ve worked with."</p>
        <span>— Former Principal, The Industry School</span>
      </div>

      <div class="testimonial">
        <p>"His knowledge and community-first attitude make SharpLync something special."</p>
        <span>— Tech Director, Regional Education Partner</span>
      </div>

      <div class="testimonial">
        <p>"A great communicator and problem solver — highly recommended for small business support."</p>
        <span>— Local Business Owner, Stanthorpe</span>
      </div>
    </div>
  </section>
</section>

<!-- ✅ Simple cycling script -->
@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const testimonials = document.querySelectorAll('.testimonial');
    let index = 0;
    setInterval(() => {
      testimonials[index].classList.remove('active');
      index = (index + 1) % testimonials.length;
      testimonials[index].classList.add('active');
    }, 6000);
  });
</script>
@endpush
@endsection