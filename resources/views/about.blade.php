@extends('layouts.base')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/about.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
@endpush

@section('title', 'SharpLync | About')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<section class="content-hero fade-in">

  {{-- ===================== --}}
  {{-- About SharpLync Title --}}
  {{-- ===================== --}}
  <div class="about-title-wrapper fade-section">
      <h1 class="about-title">
          About <span class="gradient">SharpLync</span>
      </h1>      
  </div>

  {{-- =============================================== --}}
  {{-- My Story — Split Layout White Card (Option A)   --}}
  {{-- =============================================== --}}
  <div class="story-card-split fade-section">

      {{-- Left image (hidden on mobile via CSS) --}}
      <div class="story-image">
          <img src="{{ asset('images/mystory.png') }}" alt="My Story">
      </div>

      {{-- Right column: script title + content --}}
      <div class="story-text">
          <div class="story-script-title">My Story</div>
          <h3>From Tools to Technology</h3>

          <div id="storyIntro">
              <p>My journey into technology didn’t start in a lab or an office. It started with a set of tools, cables, and a good dose of curiosity.</p>

              <p>I began my career as an Electrical Fitter, learning the value of precision, safety, and doing things properly the first time. From there, my interest naturally shifted toward the growing world of data and communication, where I started working on network cabling, PABX phone systems, and fibre optics. It was hands-on, practical work that taught me how every wire and connection plays a part in keeping a business running smoothly.</p>

              <p>As technology evolved, so did I. I moved into the IT world, working as a Computer Technician for Harvey Norman, helping people get their systems up and running, and just as importantly, making sure they actually understood how to use them.</p>
          </div>

          {{-- Hidden full story --}}
          <div id="storyFull" class="collapsed">
              <p>That experience showed me how much people appreciate honest, down-to-earth support, the kind that doesn’t rely on jargon.</p>

              <p>In the early 2000s, I took a leap and started my own business. It grew quickly, built on trust, reliability, and word-of-mouth, the old-fashioned way. Things went so well that the business was amalgamated into a larger company, giving me the chance to see how IT operates at scale.</p>

              <p>From there, I stepped into the corporate world as a Systems Administrator, managing infrastructure and supporting teams that relied on technology every day. That role led to a new chapter, one that would last over a decade in education.</p>

              <h3>Establishing Expertise at Scale</h3>
              <p>During my time working for a large school network, I helped upgrade two existing campuses and build the IT infrastructure for four new ones, everything from networking and servers to Wi-Fi, printers, cloud infrastructure, and device management. It was a massive challenge, but it shaped the way I see technology: not just as wires and code, but as something that connects people and helps them learn, grow, and succeed.</p>

              <h3>The Launch of SharpLync: Seizing an Opportunity</h3>
              <p>After more than a decade managing complex, multi-site infrastructure, I had a unique vantage point. I saw clearly what high-level, practical IT support looks like, and what was often missing for growing businesses. It became obvious that many organisations struggle to access proven, enterprise-level expertise without the massive price tag. They deserve better than generic fixes.</p>

              <p>Launching <strong>SharpLync</strong> was a proactive decision. It was the moment to take my entire range of skills—from the electrical fitter's precision to the system administrator's strategic vision—and focus them entirely on helping businesses get IT right.</p>

              <p>I believe in Straightforward Support support with modern results: being reliable, approachable, and genuinely invested in helping people make the most of their technology. Because at the end of the day, it’s not just about systems, it’s about people.</p>
          </div>
          <hr class="story-divider">
          <div class="story-signature">
              <div class="sig-name">Jannie Brits</div>
              <div class="sig-role">Founder & Lead Engineer</div>

              <a href="https://www.linkedin.com/in/jcbrits/" 
                class="sig-linkedin-modern" target="_blank" aria-label="LinkedIn Profile">
                  <svg viewBox="0 0 24 24" class="linkedin-svg">
                      <path d="M4.98 3.5C4.98 4.88 3.86 6 2.5 6S0 4.88 0 3.5 1.12 1 2.5 1s2.48 1.12 2.48 2.5zM.5 8h4V24h-4V8zm7.5 0h3.8v2.2h.1c.5-1 1.7-2.2 3.9-2.2 4.2 0 5 2.8 5 6.4V24h-4v-7.8c0-1.9 0-4.3-2.6-4.3s-3 2-3 4.1V24h-4V8z"/>
                  </svg>
              </a>

          </div>

          <div class="story-testimonial-link">
              <a href="/testimonials">Read what others say</a>
          </div>

          <button id="toggleStory" class="toggle-btn">Continue My Story...</button>
      </div>
  </div>      
  </section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  // === Expandable Story ===
  const toggleBtn = document.getElementById('toggleStory');
  const storyFull = document.getElementById('storyFull');
  let expanded = false;

  if (toggleBtn && storyFull) {
    toggleBtn.addEventListener('click', () => {
      expanded = !expanded;
      storyFull.classList.toggle('collapsed', !expanded);
      toggleBtn.textContent = expanded ? 'Show Less' : 'Continue My Story...';
    });
  }

  // === Testimonials Slider (slower + dots) ===
  const testimonials = document.querySelectorAll('.testimonials-section .testimonial');
  const container    = document.querySelector('.testimonials-section .testimonial-container');
  const dotsHolder   = document.querySelector('.testimonials-section .testimonial-dots');

  let index = 0;
  let intervalId = null;
  let dots = [];

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

  function goTo(i) {
    if (!testimonials.length) return;
    index = (i + testimonials.length) % testimonials.length;

    testimonials.forEach((card, idx) => {
      card.classList.toggle('active', idx === index);
    });

    if (dots.length) {
      dots.forEach((dot, idx) => {
        dot.classList.toggle('active', idx === index);
      });
    }
  }

  function startCycle() {
    if (intervalId || testimonials.length <= 1) return;
    intervalId = setInterval(() => {
      goTo(index + 1);
    }, 20000); // 20 seconds per slide
  }

  function stopCycle() {
    if (!intervalId) return;
    clearInterval(intervalId);
    intervalId = null;
  }

  function buildDots() {
    if (!dotsHolder || !testimonials.length) return;

    dotsHolder.innerHTML = '';
    dots = [];

    testimonials.forEach((card, i) => {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.className = 'testimonial-dot' + (i === 0 ? ' active' : '');
      dot.setAttribute('aria-label', 'Show testimonial ' + (i + 1));
      dot.dataset.index = i;

      dot.addEventListener('click', () => {
        stopCycle();
        goTo(i);
        startCycle();
      });

      dotsHolder.appendChild(dot);
      dots.push(dot);
    });
  }

  if (testimonials.length) {
    goTo(0);
    sizeCarousel();
    buildDots();
    startCycle();

    window.addEventListener('resize', () => {
      clearTimeout(window.__aboutTestimonialsResize);
      window.__aboutTestimonialsResize = setTimeout(sizeCarousel, 150);
    });
  }

  // === Modal (Version A) ===
  const modal        = document.getElementById('testimonialModal');
  const modalText    = document.getElementById('testimonialModalText');
  const modalName    = document.getElementById('testimonialModalName');
  const modalRole    = document.getElementById('testimonialModalRole');
  const closeBtn     = modal ? modal.querySelector('.testimonial-modal-close') : null;
  const backdrop     = modal ? modal.querySelector('.testimonial-modal-backdrop') : null;
  const readMoreBtns = document.querySelectorAll('.testimonial-read-more');

  function openModal(fromCard) {
    if (!modal) return;

    const fullText = fromCard?.dataset.fulltext || '';
    const name     = fromCard?.dataset.name || '';
    const who      = fromCard?.dataset.who || '';

    if (modalText) modalText.textContent = fullText;
    if (modalName) modalName.textContent = name;

    if (modalRole) {
        modalRole.textContent = who;
        modalRole.style.display = who ? 'block' : 'none';
    }

    stopCycle();
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
    modal.setAttribute('aria-hidden', 'false');
  }

  function closeModal() {
    if (!modal) return;
    modal.classList.remove('open');
    document.body.style.overflow = '';
    modal.setAttribute('aria-hidden', 'true');
    startCycle();
  }

  if (readMoreBtns.length && modal) {
    readMoreBtns.forEach(btn => {
      btn.addEventListener('click', (e) => {
        const card = e.currentTarget.closest('.testimonial');
        if (!card) return;
        openModal(card);
      });
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener('click', closeModal);
  }
  if (backdrop) {
    backdrop.addEventListener('click', closeModal);
  }
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal?.classList.contains('open')) {
      closeModal();
    }
  });
});
</script>
@endpush
@endsection
