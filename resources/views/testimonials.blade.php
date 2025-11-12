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
      /* Carousel container */
      .testimonial-carousel {
        position: relative;
        max-width: 940px;
        margin: 0 auto;
        overflow: hidden;
        padding: 10px;
      }

      /* Track (slides wrapper) */
      .testimonial-track {
        display: flex;
        transition: transform 0.8s ease-in-out;
      }

      /* Individual cards */
      .testimonial-slide {
        flex: 0 0 100%;
        box-sizing: border-box;
        padding: 0 20px;
      }

      .testimonial-card blockquote {
        margin: 0;
        line-height: 1.7;
      }

      .testimonial-card .who {
        display: block;
        margin-top: 12px;
        opacity: 0.85;
      }

      .testimonial-card .rating {
        margin-top: 10px;
        font-weight: 700;
        letter-spacing: .5px;
      }

      /* Dots */
      .testimonial-dots {
        text-align: center;
        margin-top: 22px;
      }

      .dot {
        height: 12px;
        width: 12px;
        margin: 0 5px;
        background-color: transparent;
        border: 2px solid #104946;
        border-radius: 50%;
        display: inline-block;
        cursor: pointer;
        transition: background-color 0.3s;
      }

      .dot.active {
        background-color: #104946;
      }

      /* Responsive */
      @media (max-width: 980px){
        .testimonial-slide { padding: 0 10px; }
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

          <div class="testimonial-slide">
            <article class="content-card testimonial-card">
              <blockquote>“{{ $t->testimonial_text }}”</blockquote>
              <span class="who">{{ $who }}</span>
              @if($stars)
                <div class="rating" aria-label="Rating: {{ $t->rating }} out of 5">{{ $stars }}</div>
              @endif
            </article>
          </div>
        @empty
          <div class="testimonial-slide">
            <article class="content-card">
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

      // Pause when hovered
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