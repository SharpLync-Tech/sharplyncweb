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
                    // Build "who" line
                    $parts = [];
                    if ($t->customer_position) $parts[] = $t->customer_position;
                    if ($t->customer_company)  $parts[] = $t->customer_company;
                    $who = implode(' — ', $parts);

                    // Initials
                    $nameParts = preg_split('/\s+/', trim($t->customer_name));
                    $initials = '';
                    foreach ($nameParts as $p) {
                        if ($p !== '') $initials .= mb_substr($p, 0, 1);
                    }

                    // Preview text
                    $preview = Str::limit(strip_tags($t->testimonial_text), 260);
                @endphp

                {{-- First real slide (will be active on load) --}}
                <div class="tl-slide {{ $loop->first ? 'active' : '' }}">
                    <article class="tl-card"
                        data-fulltext="{{ e($t->testimonial_text) }}"
                        data-name="{{ e($t->customer_name) }}"
                        data-who="{{ e($who) }}">
                        <div class="initial-badge">{{ $initials }}</div>
                        <blockquote>“{{ $preview }}”</blockquote>
                        <div class="tl-name">{{ $t->customer_name }}</div>
                        @if($who)<div class="tl-role">{{ $who }}</div>@endif
                    </article>
                </div>
            @empty
                <div class="tl-slide active">
                    <article class="tl-card">
                        <blockquote>“No testimonials are available yet. Please check back soon — we’re just getting started.”</blockquote>
                        <div class="tl-name">SharpLync</div>
                        <div class="tl-role">Old school support, modern results.</div>
                    </article>
                </div>
            @endforelse

            {{-- === INFINITE LOOP: Duplicate first slide at the end === --}}
            @if($testimonials->count() > 1)
                @php
                    $first = $testimonials->first();
                    $parts = [];
                    if ($first->customer_position) $parts[] = $first->customer_position;
                    if ($first->customer_company)  $parts[] = $first->customer_company;
                    $whoFirst = implode(' — ', $parts);
                    $nameParts = preg_split('/\s+/', trim($first->customer_name));
                    $initialsFirst = '';
                    foreach ($nameParts as $p) if($p!=='') $initialsFirst .= mb_substr($p,0,1);
                    $previewFirst = Str::limit(strip_tags($first->testimonial_text), 260);
                @endphp
                <div class="tl-slide">
                    <article class="tl-card"
                        data-fulltext="{{ e($first->testimonial_text) }}"
                        data-name="{{ e($first->customer_name) }}"
                        data-who="{{ e($whoFirst) }}">
                        <div class="initial-badge">{{ $initialsFirst }}</div>
                        <blockquote>“{{ $previewFirst }}”</blockquote>
                        <div class="tl-name">{{ $first->customer_name }}</div>
                        @if($whoFirst)<div class="tl-role">{{ $whoFirst }}</div>@endif
                    </article>
                </div>
            @endif

            {{-- === INFINITE LOOP: Duplicate last slide at the beginning (only if >1) === --}}
            @if($testimonials->count() > 1)
                @php
                    $last = $testimonials->last();
                    $parts = [];
                    if ($last->customer_position) $parts[] = $last->customer_position;
                    if ($last->customer_company)  $parts[] = $last->customer_company;
                    $whoLast = implode(' — ', $parts);
                    $nameParts = preg_split('/\s+/', trim($last->customer_name));
                    $initialsLast = '';
                    foreach ($nameParts as $p) if($p!=='') $initialsLast .= mb_substr($p,0,1);
                    $previewLast = Str::limit(strip_tags($last->testimonial_text), 260);
                @endphp
                <div class="tl-slide">
                    <article class="tl-card"
                        data-fulltext="{{ e($last->testimonial_text) }}"
                        data-name="{{ e($last->customer_name) }}"
                        data-who="{{ e($whoLast) }}">
                        <div class="initial-badge">{{ $initialsLast }}</div>
                        <blockquote>“{{ $previewLast }}”</blockquote>
                        <div class="tl-name">{{ $last->customer_name }}</div>
                        @if($whoLast)<div class="tl-role">{{ $whoLast }}</div>@endif
                    </article>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal for full testimonial --}}
    <div id="testimonialModal" aria-hidden="true">
        <div class="modal-dialog" role="dialog" aria-modal="true">
            <button type="button" class="modal-close" aria-label="Close testimonial">×</button>
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
    const track   = document.querySelector(".tl-track");
    const slides  = document.querySelectorAll(".tl-slide");
    const dots    = document.querySelectorAll(".tl-dot");
    const realCount = dots.length;           // actual testimonials
    let current   = 0;                       // 0 = first real testimonial
    let slideWidth = 0;
    let currentTranslate = 0;
    let prevTranslate    = 0;
    let isDragging = false;
    let startX = 0;
    let autoTimer;

    // There are realCount + 2 slides (clone last → real slides → clone first)
    const totalSlides = slides.length;

    function updateSlideWidth() {
        if (!slides.length) return;
        const s = slides[0];
        const style = getComputedStyle(s);
        slideWidth = s.offsetWidth + 
                     parseFloat(style.marginLeft || 0) + 
                     parseFloat(style.marginRight || 0) + 
                     30; // gap
    }

    function setPosition() {
        track.style.transition = "transform 0.7s cubic-bezier(0.4, 0, 0.2, 1)";
        track.style.transform = `translateX(${currentTranslate}px)`;
    }

    function goTo(realIndex) {
        current = realIndex;

        // Position = clone-last + real slides + clone-first → we add +1 because of the leading clone
        const visualIndex = current + 1;
        currentTranslate = -visualIndex * slideWidth;
        prevTranslate = currentTranslate;

        setPosition();

        // Active class on the correct visual slide
        slides.forEach((s, i) => s.classList.toggle("active", i === visualIndex));

        // Dots always reflect real index
        dots.forEach((d, i) => d.classList.toggle("active", i === current));
    }

    // Seamless infinite loop when reaching the end
    function checkBoundaries() {
        const visualIndex = current + 1;

        // Reached the cloned first slide at the end → jump to real first without transition
        if (visualIndex >= realCount + 1) {
            current = 0;
            track.style.transition = "none";
            currentTranslate = -slideWidth;           // position of first real slide
            setPosition();
            requestAnimationFrame(() => {
                track.style.transition = "transform 0.7s cubic-bezier(0.4, 0, 0.2, 1)";
                goTo(0);
            });
        }

        // Went before the first real slide → jump to cloned last slide at the beginning
        if (visualIndex < 1) {
            current = realCount - 1;
            track.style.transition = "none";
            currentTranslate = -(realCount * slideWidth);
            setPosition();
            requestAnimationFrame(() => {
                track.style.transition = "transform 0.7s cubic-bezier(0.4, 0, 0.2, 1)";
                goTo(current);
            });
        }
    }

    function next() {
        current++;
        goTo(current);
        checkBoundaries();
    }

    function prev() {
        current--;
        goTo(current);
        checkBoundaries();
    }

    function startAuto() {
        stopAuto();
        autoTimer = setInterval(next, 7000);
    }
    function stopAuto() { clearInterval(autoTimer); }

    // Drag / Touch
    function touchStart(e) {
        stopAuto();
        isDragging = true;
        startX = e.type.includes("mouse") ? e.pageX : e.touches[0].clientX;
        prevTranslate = currentTranslate;
    }
    function touchMove(e) {
        if (!isDragging) return;
        const x = e.type.includes("mouse") ? e.pageX : e.touches[0].clientX;
        currentTranslate = prevTranslate + (x - startX);
        track.style.transition = "none";
        track.style.transform = `translateX(${currentTranslate}px)`;
    }
    function touchEnd() {
        if (!isDragging) return;
        isDragging = false;

        const movedBy = currentTranslate - prevTranslate;
        if (Math.abs(movedBy) > 50) {
            movedBy < 0 ? next() : prev();
        } else {
            goTo(current);
        }
        startAuto();
    }

    slides.forEach(slide => {
        slide.addEventListener("dragstart", e => e.preventDefault());
        slide.addEventListener("mousedown", touchStart);
        slide.addEventListener("mousemove", touchMove);
        slide.addEventListener("mouseup", touchEnd);
        slide.addEventListener("mouseleave", touchEnd);
        slide.addEventListener("touchstart", touchStart);
        slide.addEventListener("touchmove", touchMove);
        slide.addEventListener("touchend", touchEnd);
    });

    // Dots
    dots.forEach((dot, i) => dot.addEventListener("click", () => {
        current = i;
        goTo(current);
        startAuto();
    }));

    // Init
    updateSlideWidth();
    currentTranslate = -slideWidth;   // first real slide centered (last clone visible on left)
    prevTranslate = currentTranslate;
    setPosition();
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