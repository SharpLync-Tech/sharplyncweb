@extends('layouts.content')

@section('title', 'SharpLync | Testimonials')

@section('content')
<section class="content-hero fade-in">
  <div class="content-header fade-section">
    <h1>Customer <span class="highlight">Testimonials</span></h1>
    <p>Real words from the people and organisations we’ve supported.</p>
  </div>

  <div class="fade-section" style="margin-top:22px;">
    <style>
      /* === Carousel Container === */
      .testimonial-carousel {
        position: relative;
        max-width: 940px;
        margin: 0 auto;
        overflow: hidden;
        padding: 10px;
      }

      /* === Track (slides wrapper) === */
      .testimonial-track {
        display: flex;
        transition: transform 0.8s ease-in-out;
      }

      /* === Individual slides === */
      .testimonial-slide {
        flex: 0 0 100%;
        box-sizing: border-box;
        padding: 0 20px;
      }

      /* === Glassy Card Styling === */
      .testimonial-card {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 16px;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
        color: #fff;
        padding: 2.2rem;
        max-width: 940px;
        margin: 0 auto;
        position: relative;
        transition: box-shadow 0.5s ease, transform 0.5s ease;
      }

      /* Soft glow on active card */
      .testimonial-slide.active .testimonial-card {
        box-shadow: 0 0 24px rgba(44, 191, 174, 0.45);
        transform: scale(1.01);
      }

      /* Decorative quote mark */
      .testimonial-card::before {
        content: "❝";
        font-size: 110px;
        color: rgba(255, 255, 255, 0.08);
        position: absolute;
        top: -45px;
        left: 25px;
        z-index: 0;
      }

      /* Text styling */
      .testimonial-card blockquote {
        margin: 0;
        line-height: 1.7;
        font-size: 1.05rem;
        position: relative;
        z-index: 1;
      }

      .testimonial-card .who {
        display: block;
        margin-top: 14px;
        font-weight: 500;
        color: #dff9f6;
        opacity: 0.95;
      }

      .testimonial-card .rating {
        margin-top: 10px;
        font-weight: 700;
        letter-spacing: .5px;
        color: #2CBFAE;
      }

      /* === Dots === */
      .testimonial-dots {
        text-align: center;
        margin-top: 26px;
      }

      .dot {
        height: 12px;
        width: 12px;
        margin: 0 5px;
        background-color: transparent;
        border: 2px solid #2CBFAE;
        border-radius: 50%;
        display: inline-block;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.3s;
      }

      .dot.active {
        background-color: #2CBFAE;
        transform: scale(1.15);
      }

      /* === Responsive === */
      @media (max-width: 980px){
        .testimonial-slide { padding: 0 10px; }
        .testimonial-card { padding: 1.6rem; font-size: 0.95rem; }
      }
    </style>

    <div class="testimonial-carousel" id="testimonialCarousel">
      <div class="testimonial-track" id="testimonialTrack">
        @forelse($testimonials as $t)
          @php
            $who = trim(($t->customer_name ?: '') .
                        (($t->customer_position || $t->customer_company) ? ' — ' : '') .
                        ($t->customer_position ?: '') .
                        (($t->customer_position && $t->customer_company) ? ' — ' : '') .
                        ($t->customer_company ?: ''));
            $stars = $t->rating ? str_repeat('★', (int)$t->rating) . str_repeat('☆', 5 - (int)$t->rating) : null;
          @endphp

          <div class="testimonial-slide {{ $loop->first ? 'active' : '' }}">
            <article class="content-card testimonial-card">
              <blockquote>“{{ $t->testimonial_text }}”</blockquote>
              <span class="who">{{ $who }}</span>
              @if($stars)
                <div class="rating" aria-label="Rating: {{ $t->rating }} out of 5">{{ $stars }}</div>
              @endif
            </article>
          </div>
        @empty
          <div class="testimonial-slide active">
            <article class="content-card testimonial-card">
              <p>No testimonials available yet. Please check back soon.</p>
            </article>
          </div>
        @endforelse
      </div>

      <div class="testimonial-dots" id="testimonialDots">
        @foreach($testimonials as $index => $t)
          <span class="dot {{ $loop->first ? 'active' : '' }}" data-index="{{ $index }}"></span>
        @endforeach
      </div>
    </div>

    <script>
      const track = document.getElementById('testimonialTrack');
      const dots = document.querySelectorAll('#testimonialDots .dot');
      const carousel = document.getElementById('testimonialCarousel');
      let currentIndex = 0;
      let autoSlide;

      function showSlide(index) {
        const slides = document.querySelectorAll('.testimonial-slide');
        if (!slides.length) return;
        currentIndex = (index + slides.length) % slides.length;
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
        slides.forEach((slide, i) => slide.classList.toggle('active', i === currentIndex));
        dots.forEach((dot, i) => dot.classList.toggle('active', i === currentIndex));
      }

      function nextSlide() {
        showSlide(currentIndex + 1);
      }

      function startAutoSlide() {
        autoSlide = setInterval(nextSlide, 6000); // 6-second delay
      }

      function stopAutoSlide() {
        clearInterval(autoSlide);
      }

      // Pause on hover
      carousel.addEventListener('mouseenter', stopAutoSlide);
      carousel.addEventListener('mouseleave', startAutoSlide);

      // Dots click
      dots.forEach(dot => {
        dot.addEventListener('click', () => {
          const index = parseInt(dot.getAttribute('data-index'));
          showSlide(index);
        });
      });

      startAutoSlide();
    </script>
  </div>
</section>
@endsection