@extends('layouts.sharpfleet')

@section('title', 'How SharpFleet Started')

@push('styles')
    <link rel="stylesheet" href="{{ secure_asset('css/sharpfleet/sharpfleet-about.css') }}?v={{ @filemtime(public_path('css/sharpfleet/sharpfleet-about.css')) ?: time() }}">
@endpush

@section('sharpfleet-content')

<div class="card sf-about-card">
    <div class="card-body">

        {{-- =========================
             PAGE HERO
        ========================== --}}
        <header class="sf-about-hero">
            <h1 class="sf-about-title">
                <span>How SharpFleet</span><br>
                <span>Started</span>
            </h1>
            <span class="sf-about-underline"></span>
        </header>

        {{-- =========================
             STORY
        ========================== --}}
        <div class="sf-about-story">

            <p>I started my own business and needed to log business trips.</p>

            <p>
                I had two cars.<br>
                I was constantly swapping between them.
            </p>

            <p>
                Half the time the logbook wasn’t in the car I was driving.<br>
                And when it was, I couldn’t be bothered writing things down anyway.
            </p>

            <p>
                I don’t like writing.<br>
                I don’t like trying to remember trips later.<br>
                And I really didn’t like that something so simple was already becoming a pain.
            </p>

            <p>
                So I built my own logbook — on my phone.<br>
                Something I knew would always be with me, no matter which car I was using.
            </p>

            <p>
                It started as a really basic tool just for me.<br>
                Something I could actually use without thinking about it.
            </p>

            <p>Then I added a bit here and there as I needed it.</p>

            <p>
                Services.<br>
                Rego.<br>
                Vehicles.<br>
                Small improvements.
            </p>

            <p>As it grew, I realised something else:</p>

            <p class="sf-about-key">
                Business owners should be the ones setting the rules.
            </p>

            <p>
                Not software.<br>
                Not rigid systems.
            </p>

            <p>
                So I built SharpFleet to give business owners the tools,<br>
                and let them decide how strict or flexible they want to be.
            </p>

            <p>
                It wasn’t planned.<br>
                There was no big idea behind it.
            </p>

            <p>
                It just grew out of fixing a problem I didn’t want to deal with anymore.
            </p>

            <p class="sf-about-close">
                That’s it. That’s how it started.<br>
                Now it’s available to you — set the rules for your business,<br>
                and let your drivers… well, drive 🚗
            </p>

        </div>
    </div>
</div>

@endsection
