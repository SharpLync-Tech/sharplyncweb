<!-- =====================
     Works on Desktop & Mobile.
     Hamburge menu not overlaying. 
     Modals working.    
     ===================== -->

@extends('layouts.about-base')

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

              <p>I believe in old school support with modern results: being reliable, approachable, and genuinely invested in helping people make the most of their technology. Because at the end of the day, it’s not just about systems, it’s about people.</p>
          </div>
          <div class="story-signoff">
              <p class="signoff-name">Jannie Brits</p>
              <p class="signoff-title">Founder & Lead Engineer</p>

              <a href="https://www.linkedin.com/in/jcbrits/" 
                target="_blank" 
                rel="noopener" 
                class="signoff-linkedin">
                  <img src="{{ asset('images/linkedin.png') }}" alt="LinkedIn" />
              </a>
          </div>

          <button id="toggleStory" class="toggle-btn">Continue My Story...</button>
      </div>
  </div>

  {{-- ===================== --}}
  {{-- Testimonials Section --}}
  {{-- ===================== --}}
  <section class="testimonials-section fade-section">
    <h3>What People Say</h3>

    <div class="testimonial-wrapper">
      <div class="testimonial-container">
        @forelse($testimonials as $t)
          @php
            $who = trim(
              ($t->customer_position ? $t->customer_position : '') .
              (($t->customer_position && $t->customer_company) ? ' — ' : '') .
              ($t->customer_company ? $t->customer_company : '')
            );

            // Short preview for About page (adjust 320 if you want more/less)
            $preview = Str::limit(strip_tags($t->testimonial_text), 320);
          @endphp

          <div
              class="testimonial {{ $loop->first ? 'active' : '' }}"
              data-fulltext="{{ e($t->testimonial_text) }}"
              data-name="{{ e($t->customer_name) }}"
              data-who="{{ e($who) }}"
          >
            <div class="testimonial-meta">
              <h4>{{ $t->customer_name }}</h4>
              @if($who)
                <p class="testimonial-role">{{ $who }}</p>
              @endif
            </div>

            <p class="testimonial-preview">"{{ $preview }}"</p>

            <button type="button" class="testimonial-read-more">
              Read more…
            </button>
          </div>
        @empty
          {{-- Fallback if no testimonials in DB --}}
          <div
            class="testimonial active"
            data-fulltext="Jannie is one of the most dependable and dedicated IT professionals I’ve worked with."
            data-name="Former Principal"
            data-who="The Industry School"
          >
            <div class="testimonial-meta">
              <h4>Former Principal</h4>
              <p class="testimonial-role">The Industry School</p>
            </div>
            <p class="testimonial-preview">
              "Jannie is one of the most dependable and dedicated IT professionals I’ve worked with."
            </p>
            <button type="button" class="testimonial-read-more">
              Read more…
            </button>
          </div>
        @endforelse
      </div>

      {{-- Dot navigation --}}
      <div class="testimonial-dots" aria-label="Testimonial navigation"></div>
    </div>
  </section>

  {{-- ===================== --}}
  {{-- Testimonial Modal     --}}
  {{-- ===================== --}}
  <div id="testimonialModal" class="testimonial-modal" aria-hidden="true">
    <div class="testimonial-modal-backdrop"></div>

    <div class="testimonial-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="testimonialModalName">
      <button type="button" class="testimonial-modal-close" aria-label="Close testimonial">&times;</button>

      <div class="testimonial-modal-inner">
        <div class="testimonial-modal-quote"></div>

        <p class="testimonial-modal-text" id="testimonialModalText"></p>

        <div class="testimonial-modal-separator"></div>

        <p class="testimonial-modal-name" id="testimonialModalName"></p>
        <p class="testimonial-modal-role" id="testimonialModalRole"></p>
      </div>
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
      toggleBtn.textContent = expanded ? 'Show Less –' : 'Continue My Story...';
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
