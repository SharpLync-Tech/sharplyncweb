@extends('layouts.base')

@section('title', 'SharpLync Facilities | About')

@section('content')
<section class="content-hero fade-in">
  <div class="content-header fade-section">
    <h1>About <span class="highlight">SharpLync Facilities</span></h1>
    <p>Extending SharpLync's legacy of reliable IT support to smart facilities management — from Granite Belt roots to nationwide operations.</p>
  </div>

  <!-- ===================== -->
  <!-- Our Story Section -->
  <!-- ===================== -->
  <div class="content-card fade-section">
    <h3>Our Story: From IT Foundations to Facilities Excellence</h3>

    <div id="storyIntro">
      <p>SharpLync Facilities builds on a foundation of hands-on technology expertise, now focused on streamlining facility operations with the same precision and care that powers our core IT services.</p>

      <p>Starting from electrical fitting and network infrastructure, we've evolved to manage complex multi-site environments, including educational campuses and business fleets, ensuring every asset and task is handled with local reliability.</p>
    </div>

    <!-- Hidden full story -->
    <div id="storyFull" class="collapsed">
      <p>Our journey took us through roles in systems administration and large-scale IT deployments, where we learned the critical balance between technology and practical operations.</p>

      <p>With experience upgrading campuses and building new infrastructures — from servers and Wi-Fi to device management — we saw the need for integrated facilities solutions that don't just track, but empower businesses.</p>

      <h3>Why Facilities Matter to Us</h3>
      <p>Launching SharpLync Facilities was a natural extension: combining enterprise-level tools for sites, projects, assets, and fleets with the personal touch that defines us. We handle everything from site addresses and budgets to vehicle VINs and maintenance tasks, all tied seamlessly to your IT ecosystem.</p>

      <p>Old school reliability meets modern facilities tech — because smart management starts with people who care.</p>
    </div>

    <button id="toggleStory" class="toggle-btn">Continue Our Story +</button>
  </div>

  <!-- ===================== -->
  <!-- Testimonials Section -->
  <!-- ===================== -->
  <section class="testimonials-section fade-section">
    <h3>What Our Clients Say</h3>

    <div class="testimonial-wrapper">
      <button class="nav-btn prev" aria-label="Previous testimonial">
        <svg viewBox="0 0 24 24" aria-hidden="true">
          <circle cx="12" cy="12" r="10"></circle>
          <path d="M13.5 7.5 9 12l4.5 4.5"></path>
        </svg>
      </button>

      <div class="testimonial-container">
        {{-- Static fallbacks; integrate DB later --}}
        <div class="testimonial active">
          <p>"SharpLync Facilities transformed our multi-site operations — tracking assets and projects has never been easier."</p>
          <span>— Operations Manager, Granite Belt Logistics</span>
        </div>
        <div class="testimonial">
          <p>"Reliable fleet management with real-time insights. Their integration with our IT systems is seamless."</p>
          <span>— Fleet Supervisor, Regional Transport Co.</span>
        </div>
        <div class="testimonial">
          <p>"From automation to compliance, they've got us covered. Local expertise with big results."</p>
          <span>— Facilities Director, Education Network</span>
        </div>
      </div>

      <button class="nav-btn next" aria-label="Next testimonial">
        <svg viewBox="0 0 24 24" aria-hidden="true">
          <circle cx="12" cy="12" r="10"></circle>
          <path d="M10.5 7.5 15 12l-4.5 4.5"></path>
        </svg>
      </button>
    </div>
  </section>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  // === Expandable Story ===
  const toggleBtn = document.getElementById('toggleStory');
  const storyFull = document.getElementById('storyFull');
  let expanded = false;

  toggleBtn.addEventListener('click', () => {
    expanded = !expanded;
    storyFull.classList.toggle('collapsed', !expanded);
    toggleBtn.textContent = expanded ? 'Show Less –' : 'Continue Our Story +';
  });

  // === Testimonials Carousel ===
  const testimonials = document.querySelectorAll('.testimonial');
  const nextBtn = document.querySelector('.nav-btn.next');
  const prevBtn = document.querySelector('.nav-btn.prev');
  const container = document.querySelector('.testimonial-container');
  let index = 0, interval;

  function sizeCarousel() {
    if (!container || !testimonials.length) return;
    let maxH = 0;
    testimonials.forEach(card => {
      const wasActive = card.classList.contains('active');
      const prevStyle = card.getAttribute('style') || '';
      card.style.position = 'absolute';
      card.style.visibility = 'hidden';
      card.style.display = 'block';
      card.classList.add('active');
      maxH = Math.max(maxH, card.scrollHeight);
      card.setAttribute('style', prevStyle);
      if (!wasActive) card.classList.remove('active');
    });
    container.style.minHeight = (maxH + 24) + 'px';
  }

  function showTestimonial(i) {
    testimonials.forEach((t, idx) => t.classList.toggle('active', idx === i));
  }
  function startCycle() {
    interval = setInterval(() => {
      index = (index + 1) % testimonials.length;
      showTestimonial(index);
    }, 6000);
  }
  function stopCycle() { clearInterval(interval); }

  if (testimonials.length) {
    showTestimonial(index);
    sizeCarousel();
    startCycle();
    window.addEventListener('resize', () => {
      clearTimeout(window.__tc);
      window.__tc = setTimeout(sizeCarousel, 150);
    });

    nextBtn.addEventListener('click', () => { stopCycle(); index = (index + 1) % testimonials.length; showTestimonial(index); startCycle(); });
    prevBtn.addEventListener('click', () => { stopCycle(); index = (index - 1 + testimonials.length) % testimonials.length; showTestimonial(index); startCycle(); });
  }
});
</script>
@endpush
@endsection