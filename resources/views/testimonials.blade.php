@extends('layouts.testimonials-base')

@section('title', 'SharpLync | Testimonials')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<section class="testimonials-page">

    {{-- Page heading --}}
    <div class="testimonials-title">
        <h1>Customer Testimonials</h1>
        <p>Real words from the people and organisations we’ve supported.</p>
    </div>

    {{-- Dots above the carousel --}}
    <div class="tl-dots" id="tlDots">
        @foreach($testimonials as $index => $t)
            <span class="tl-dot {{ $loop->first ? 'active' : '' }}" data-index="{{ $index }}"></span>
        @endforeach
    </div>

    {{-- Carousel --}}
    <div class="tl-carousel" id="tlCarousel">
        <div class="tl-track" id="tlTrack">
            @forelse($testimonials as $t)
                @php
                    // Build "who" line - position and company
                    $parts = [];
                    if ($t->customer_position) { $parts[] = $t->customer_position; }
                    if ($t->customer_company)  { $parts[] = $t->customer_company;  }
                    $who = implode(' — ', $parts);

                    // Initials from customer name
                    $nameParts = preg_split('/\s+/', trim($t->customer_name));
                    $initials = '';
                    foreach ($nameParts as $p) {
                        if ($p !== '') {
                            $initials .= mb_substr($p, 0, 1);
                        }
                    }

                    // Preview text for card body
                    $preview = Str::limit(strip_tags($t->testimonial_text), 260);
                @endphp

                <div class="tl-slide {{ $loop->first ? 'active' : '' }}">
                    <article
                        class="tl-card"
                        data-fulltext="{{ e($t->testimonial_text) }}"
                        data-name="{{ e($t->customer_name) }}"
                        data-who="{{ e($who) }}"
                    >
                        <div class="initial-badge">{{ $initials }}</div>

                        <blockquote>“{{ $preview }}”</blockquote>

                        <div class="tl-name">{{ $t->customer_name }}</div>

                        @if($who)
                            <div class="tl-role">{{ $who }}</div>
                        @endif
                    </article>
                </div>
            @empty
                <div class="tl-slide active">
                    <article class="tl-card">
                        <blockquote>
                            “No testimonials are available yet. Please check back soon —
                            we’re just getting started.”
                        </blockquote>
                        <div class="tl-name">SharpLync</div>
                        <div class="tl-role">Old school support, modern results.</div>
                    </article>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Modal for full testimonial --}}
    <div id="testimonialModal" aria-hidden="true">
        <div class="modal-dialog" role="dialog" aria-modal="true">
            <button type="button" class="modal-close" aria-label="Close testimonial">&times;</button>

            <p id="modalText" class="modal-text"></p>

            <div class="modal-separator"></div>

            <p id="modalName" class="modal-name"></p>
            <p id="modalRole" class="modal-role"></p>
        </div>
    </div>

</section>

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
    const track = document.querySelector(".tl-track");
    const slides = Array.from(document.querySelectorAll(".tl-slide"));
    const dots = Array.from(document.querySelectorAll(".tl-dot"));

    let current = 0;
    let slideWidth = 0;
    let isDragging = false;
    let startX = 0;
    let currentTranslate = 0;
    let prevTranslate = 0;
    let animationID = 0;
    let autoTimer;

    /* -------------------------------------------------------
       Get actual slide width (because slides are ±85%)
    -------------------------------------------------------- */
    function updateSlideWidth() {
    if (slides.length === 0) return;
    const slide = slides[0];
    const style = window.getComputedStyle(slide);
    const marginLeft = parseFloat(style.marginLeft);
    const marginRight = parseFloat(style.marginRight);
    slideWidth = slide.offsetWidth + marginLeft + marginRight + 30; // 30 = gap
}

    /* -------------------------------------------------------
       Move to slide i
    -------------------------------------------------------- */
    function goTo(i) {
    current = (i + slides.length) % slides.length;

    // This centers the active slide perfectly
    const offset = (track.parentNode.offsetWidth - slides[0].offsetWidth) / 2;
    currentTranslate = offset - (current * slideWidth);
    
    prevTranslate = currentTranslate;
    setSliderPosition();

    slides.forEach((s, idx) => s.classList.toggle("active", idx === current));
    dots.forEach((d, idx) => d.classList.toggle("active", idx === current));
}

    /* -------------------------------------------------------
       Auto slide
    -------------------------------------------------------- */
    function startAuto() {
        stopAuto();
        autoTimer = setInterval(() => goTo(current + 1), 7000);
    }
    function stopAuto() {
        clearInterval(autoTimer);
    }

    /* -------------------------------------------------------
       Touch + Drag Support
    -------------------------------------------------------- */
    slides.forEach((slide, index) => {
        // Disable default image drag
        slide.addEventListener("dragstart", e => e.preventDefault());

        // Touch start
        slide.addEventListener("touchstart", touchStart(index));
        slide.addEventListener("touchend", touchEnd);
        slide.addEventListener("touchmove", touchMove);

        // Mouse drag
        slide.addEventListener("mousedown", touchStart(index));
        slide.addEventListener("mouseup", touchEnd);
        slide.addEventListener("mousemove", touchMove);
        slide.addEventListener("mouseleave", touchEnd);
    });

    function touchStart(index) {
        return function (e) {
            stopAuto();

            isDragging = true;
            startX = getX(e);
            animationID = requestAnimationFrame(animation);
        };
    }

    function touchMove(e) {
        if (!isDragging) return;
        const x = getX(e);
        const delta = x - startX;
        currentTranslate = prevTranslate + delta;
    }

    function touchEnd() {
        if (!isDragging) return;
        isDragging = false;
        cancelAnimationFrame(animationID);

        const movedBy = currentTranslate - prevTranslate;

        // threshold: must drag at least 30px
        if (movedBy < -30) goTo(current + 1);
        else if (movedBy > 30) goTo(current - 1);
        else goTo(current); // snap back

        startAuto();
    }

    function getX(e) {
        return e.type.includes("mouse") ? e.pageX : e.touches[0].clientX;
    }

    function animation() {
        setSliderPosition();
        if (isDragging) requestAnimationFrame(animation);
    }

    function setSliderPosition() {
        track.style.transform = `translateX(${currentTranslate}px)`;
    }

    /* -------------------------------------------------------
       Clickable dots
    -------------------------------------------------------- */
    dots.forEach((dot, index) =>
        dot.addEventListener("click", () => {
            goTo(index);
            startAuto();
        })
    );

    /* -------------------------------------------------------
       Init
    -------------------------------------------------------- */
    updateSlideWidth();
    goTo(0);
    startAuto();

    window.addEventListener("resize", () => {
        updateSlideWidth();
        goTo(current);
    });
});
</script>


@endpush
@endsection
