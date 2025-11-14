@extends('layouts.about-base')

@section('title', 'SharpLync | About')

@section('content')

<section class="about-page">

    <h1>About <span class="highlight">SharpLync</span></h1>
    <p style="text-align:center; max-width: 700px; margin: 0 auto 2rem;">
        From the Granite Belt to the Cloud, bridging the gap between people and technology with old school support and modern results.
    </p>

    <!-- STORY CARD -->
    <div class="about-card">
        <h3>My Story: From Tools to Technology</h3>

        <div id="storyIntro">
            <p>My journey into technology didn’t start in a lab or an office...</p>
            <p>I began my career as an Electrical Fitter...</p>
        </div>

        <div id="storyFull" class="collapsed">
            <p>That experience showed me...</p>
            <p>In the early 2000s, I took a leap...</p>
            <h3>Establishing Expertise at Scale</h3>
            <p>During my time working...</p>
            <h3>The Launch of SharpLync</h3>
            <p>After more than a decade...</p>
        </div>

        <button id="toggleStory" class="toggle-btn">Continue My Story +</button>
    </div>

    <!-- TESTIMONIALS -->
    <section class="testimonials-section">
        <h3>What People Say</h3>

        <div class="testimonial-wrapper">
            <div class="testimonial-container">

                @foreach ($testimonials as $index => $t)
                    @php
                        $who = trim(($t->customer_position ?? '') . 
                                    (($t->customer_position && $t->customer_company) ? ' — ' : '') .
                                     ($t->customer_company ?? ''));

                        $preview = \Illuminate\Support\Str::limit(strip_tags($t->testimonial_text), 300);
                    @endphp

                    <div class="testimonial {{ $index === 0 ? 'active' : '' }}"
                         data-fulltext="{{ e($t->testimonial_text) }}"
                         data-name="{{ e($t->customer_name) }}"
                         data-who="{{ e($who) }}">

                        <div class="testimonial-meta">
                            <h4>{{ $t->customer_name }}</h4>
                            @if ($who)
                                <p class="testimonial-role">{{ $who }}</p>
                            @endif
                        </div>

                        <p class="testimonial-preview">"{{ $preview }}"</p>

                        <button class="testimonial-read-more">Read more…</button>
                    </div>

                @endforeach

            </div>

            <div class="testimonial-dots"></div>
        </div>
    </section>

</section>

<!-- MODAL -->
<div id="testimonialModal" class="testimonial-modal">
    <div class="testimonial-modal-backdrop"></div>

    <div class="testimonial-modal-dialog">
        <button class="testimonial-modal-close">×</button>

        <div class="testimonial-modal-quote">“</div>

        <p id="modalText" class="testimonial-modal-text"></p>

        <div class="testimonial-modal-separator"></div>

        <p id="modalName" class="testimonial-modal-name"></p>
        <p id="modalRole" class="testimonial-modal-role"></p>
    </div>
</div>

@endsection

@push('scripts')
<script>

// STORY EXPAND
const toggleBtn = document.getElementById('toggleStory');
const fullStory = document.getElementById('storyFull');

toggleBtn.addEventListener('click', () => {
    fullStory.classList.toggle('collapsed');
    toggleBtn.textContent = fullStory.classList.contains('collapsed')
        ? 'Continue My Story +'
        : 'Show Less –';
});

// SLIDER + MODAL JS (same as previous message)

</script>
@endpush
