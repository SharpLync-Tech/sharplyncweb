@extends('layouts.testimonials-base')
@section('title', 'SharpLync | Testimonials')

@section('content')
<section class="testimonials-grid">
    <div class="container">
        <!-- Header -->
        <div class="testimonials-header">
            <h1>What Our Customers Say</h1>
            <p>Real experiences from the people and organisations we've partnered with to deliver exceptional connectivity solutions.</p>
        </div>

        <!-- Grid -->
        <div class="grid-container">
            @forelse($testimonials as $t)
                @php
                    // Build "Position — Company"
                    $who = collect([$t->customer_position, $t->customer_company])->filter()->implode(' — ');

                    // Initials
                    $initials = collect(preg_split('/\s+/', trim($t->customer_name)))
                                    ->map(fn($p) => mb_substr($p, 0, 1))
                                    ->join('');

                    // Card sizing
                    $size = $loop->first ? 'featured' : ($loop->iteration <= 3 ? 'large' : 'small');
                @endphp

                <article class="testimonial-card {{ $size }} {{ $loop->first ? 'first-card' : '' }}">
                    <div class="initials">{{ $initials }}</div>

                    <blockquote>
                        <p class="quote-text">
                            {!! Str::limit(strip_tags($t->testimonial_text), $loop->iteration <= 3 ? 520 : 240, '<span class="more">… <a href="#" class="expand-link">Read more</a></span>') !!}
                        </p>

                        @if(strlen(strip_tags($t->testimonial_text)) > ($loop->iteration <= 3 ? 520 : 240))
                            <div class="full-text">{!! nl2br(e($t->testimonial_text)) !!}</div>
                        @endif
                    </blockquote>

                    <footer class="author">
                        <strong class="name">{{ $t->customer_name }}</strong>
                        @if($who)<span class="role">{{ $who }}</span>@endif
                    </footer>
                </article>

            @empty
                <div class="empty-state">
                    <p>No testimonials yet — we're just getting started on delivering amazing results.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.expand-link').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const card = link.closest('.testimonial-card');
            card.classList.toggle('expanded');
        });
    });
});
</script>
@endpush