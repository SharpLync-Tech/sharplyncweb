@extends('layouts.content')

@section('title', 'SharpLync | About')

@section('content')
<section class="content-hero fade-in">
  <div class="content-header fade-section">
    <h1>About <span class="highlight">SharpLync</span></h1>
    <p>From the Granite Belt to the Cloud, bridging the gap between people and technology with old school support and modern results.</p>
  </div>

  <!-- ===================== -->
  <!-- My Story Section v3 -->
  <!-- ===================== -->
  <div class="content-card fade-section">
    <h3>My Story: From Tools to Technology</h3>

    <div id="storyIntro">
      <p>My journey into technology didn’t start in a lab or an office. It started with a set of tools, cables, and a good dose of curiosity.</p>

      <p>I began my career as an Electrical Fitter, learning the value of precision, safety, and doing things properly the first time. From there, my interest naturally shifted toward the growing world of data and communication, where I started working on network cabling, PABX phone systems, and fibre optics. It was hands-on, practical work that taught me how every wire and connection plays a part in keeping a business running smoothly.</p>

      <p>As technology evolved, so did I. I moved into the IT world, working as a Computer Technician for Harvey Norman, helping people get their systems up and running, and just as importantly, making sure they actually understood how to use them.</p>
    </div>

    <!-- Hidden full story -->
    <div id="storyFull" class="collapsed">
      <p>That experience showed me how much people appreciate honest, down-to-earth support, the kind that doesn’t rely on jargon.</p>

      <p>In the early 2000s, I took a leap and started my own business. It grew quickly, built on trust, reliability, and word-of-mouth, the old-fashioned way. Things went so well that the business was amalgamated into a larger company, giving me the chance to see how IT operates at scale.</p>

      <p>From there, I stepped into the corporate world as a Systems Administrator, managing infrastructure and supporting teams that relied on technology every day. That role led to a new chapter, one that would last over a decade in education.</p>

      <h3>Establishing Expertise at Scale</h3>
      <p>During my time working for a large school network, I helped upgrade two existing campuses and build the IT infrastructure for four new ones, everything from networking and servers to Wi-Fi, printers, cloud infrastructure, and device management. It was a massive challenge, but it shaped the way I see technology: not just as wires and code, but as something that connects people and helps them learn, grow, and succeed.</p>

      <h3>The Launch of SharpLync: Seizing an Opportunity</h3>
      <p>After more than a decade managing complex, multi-site infrastructure, I had a unique vantage point. I saw clearly what high-level, practical IT support looks like, and what was often missing for growing businesses. It became obvious that many organisations struggle to access proven, enterprise-level expertise without the massive price tag. They deserve better than generic fixes.</p>

      <p>Launching <strong>SharpLync</strong> was a proactive decision. It was the moment to take my entire range of skills—from the electrical fitter's precision to the system administrator's strategic vision—and focus them entirely on helping businesses get IT right.</p>

      <p>I believe in old school support with modern results: being reliable, approachable, and genuinely invested in helping people make the most of their technology. Because at the end of the day, it’s not just about systems, it’s about people.</p>
    </div>

    <button id="toggleStory" class="toggle-btn">Continue My Story +</button>
  </div>

  <!-- ===================== -->
  <!-- Testimonials Section -->
  <!-- ===================== -->
  <section class="testimonials-section fade-section">
    <h3>What People Say</h3>

    <div class="testimonial-wrapper">
      <button class="nav-btn prev" aria-label="Previous testimonial">
              <svg viewBox="0 0 24 24" aria-hidden="true">
              <circle cx="12" cy="12" r="10"></circle>
              <path d="M13.5 7.5 9 12l4.5 4.5"></path>
              </svg>
      </button>

      <div class="testimonial-container">
        @forelse($testimonials as $t)
          @php
            $who = trim(($t->customer_position ? $t->customer_position : '') .
                        (($t->customer_position && $t->customer_company) ? ' — ' : '') .
                        ($t->customer_company ? $t->customer_company : ''));
          @endphp
          <div class="testimonial {{ $loop->first ? 'active' : '' }}">
            <p>"{{ $t->testimonial_text }}"</p>
            <span>— {{ $t->customer_name }}@if($who), {{ $who }}@endif</span>
          </div>
        @empty
          {{-- Fallback if no testimonials in DB --}}
          <div class="testimonial active">
            <p>"Jannie is one of the most dependable and dedicated IT professionals I’ve worked with."</p>
            <span>— Former Principal, The Industry School</span>
          </div>
        @endforelse
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
    toggleBtn.textContent = expanded ? 'Show Less –' : 'Continue My Story +';
  });

  // === Testimonials Carousel (DB-backed) ===
  const testimonials = document.querySelectorAll('.testimonial');
  const nextBtn = document.querySelector('.nav-btn.next');
  const prevBtn = document.querySelector('.nav-btn.prev');
  const container = document.querySelector('.testimonial-container');
  let index = 0, interval;

  // Auto-size the container to the tallest testimonial so text never clips
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