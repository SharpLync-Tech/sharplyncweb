<!-- 
  Page: about.blade.php
  Version: v11.0
  Last updated: 04 Nov 2025 by Jannie & Max
  Description: About SharpLync — uses the new content layout with dedicated CSS and testimonial carousel.
-->

@extends('layouts.content')

@section('title', 'SharpLync | About')

@section('content')
<section class="content-hero fade-in">

  <!-- ===================== -->
  <!-- Page Header -->
  <!-- ===================== -->
  <div class="content-header fade-section">
    <h1>About <span class="highlight">SharpLync</span></h1>
    <p>From the Granite Belt to the Cloud — bridging the gap between people and technology with old school support and modern results.</p>
  </div>

  <!-- ===================== -->
  <!-- Story Card -->
  <!-- ===================== -->
  <div class="content-card fade-section">
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

  <!-- ===================== -->
  <!-- Testimonials -->
  <!-- ===================== -->
  <section class="testimonials-section fade-section">
    <h3>What People Say</h3>

    <div class="testimonial-wrapper">
      <button class="nav-btn prev" aria-label="Previous testimonial">❮</button>

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

      <button class="nav-btn next" aria-label="Next testimonial">❯</button>
    </div>
  </section>

</section>

<!-- ===================== -->
<!-- Testimonial Carousel Script -->
<!-- ===================== -->
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const testimonials = document.querySelectorAll('.testimonial');
  const nextBtn = document.querySelector('.nav-btn.next');
  const prevBtn = document.querySelector('.nav-btn.prev');
  let index = 0;
  let interval;

  const showTestimonial = (i) => {
    testimonials.forEach((t, idx) => {
      t.classList.toggle('active', idx === i);
    });
  };

  const startAutoCycle = () => {
    interval = setInterval(() => {
      index = (index + 1) % testimonials.length;
      showTestimonial(index);
    }, 6000);
  };

  const stopAutoCycle = () => clearInterval(interval);

  nextBtn.addEventListener('click', () => {
    stopAutoCycle();
    index = (index + 1) % testimonials.length;
    showTestimonial(index);
    startAutoCycle();
  });

  prevBtn.addEventListener('click', () => {
    stopAutoCycle();
    index = (index - 1 + testimonials.length) % testimonials.length;
    showTestimonial(index);
    startAutoCycle();
  });

  showTestimonial(index);
  startAutoCycle();
});
</script>
@endpush
@endsection