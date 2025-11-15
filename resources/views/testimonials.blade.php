@extends('layouts.testimonials-base')

@section('title', 'SharpLync | Testimonials')

@section('content')

<section class="content-hero fade-in">
  <div class="content-header fade-section">
    <h1>Customer <span class="highlight">Testimonials</span></h1>
    <p>Real words from the people and organisations we’ve supported.</p>
  </div>

  <div class="testimonial-carousel" id="testimonialCarousel">

    <div class="testimonial-dots" id="testimonialDots">
      @foreach($testimonials as $index => $t)
        <span class="dot {{ $loop->first ? 'active' : '' }}" data-index="{{ $index }}"></span>
      @endforeach
    </div>

    <div class="testimonial-track" id="testimonialTrack">
      @foreach($testimonials as $t)
        <div class="testimonial-slide {{ $loop->first ? 'active' : '' }}">
          <article class="testimonial-card">
            <blockquote>“{{ $t->testimonial_text }}”</blockquote>
            <span class="who">
              {{ $t->customer_name }}
              @if($t->customer_position || $t->customer_company)
                <span>
                  {{ $t->customer_position }}
                  @if($t->customer_position && $t->customer_company) — @endif
                  {{ $t->customer_company }}
                </span>
              @endif
            </span>
          </article>
        </div>
      @endforeach
    </div>

  </div>

</section>

@endsection

@push('scripts')
<script>
  const track = document.getElementById('testimonialTrack');
  const dots = document.querySelectorAll('#testimonialDots .dot');
  let currentIndex = 0;

  function showSlide(i) {
    currentIndex = i;
    track.style.transform = `translateX(-${i * 100}%)`;
    dots.forEach((d, idx) => d.classList.toggle('active', idx === i));
  }

  dots.forEach(d =>
    d.addEventListener('click', () =>
      showSlide(parseInt(d.dataset.index))
    )
  );

  setInterval(() => showSlide((currentIndex + 1) % dots.length), 6000);
</script>
@endpush
