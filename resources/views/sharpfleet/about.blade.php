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

            <p>
                I started SharpFleet for a simple reason — I needed a better way to log business trips.
            </p>

            <p>
                I was running my own business and using more than one vehicle.<br>
                Switching cars meant switching logbooks, and half the time the one I needed wasn’t there.
            </p>

            <p>
                And even when it was, writing things down later never worked.<br>
                Trips were forgotten, details were missed, and something that should have been simple became frustrating.
            </p>

            <p>
                So I built my own logbook — on my phone.
            </p>

            <p>
                Something I always had with me.<br>
                Something I could use in the moment, without thinking about it.
            </p>

            <p>
                It started as a basic tool, built purely to solve my own problem.<br>
                Then, as real life demanded it, I added more.
            </p>

            <p>
                Vehicles.<br>
                Rego.<br>
                Servicing.<br>
                Reminders.
            </p>

            <p>
                Small improvements that made day-to-day work easier.
            </p>

            <p>
                As SharpFleet grew, one idea became clear:
            </p>

            <p class="sf-about-key">
                Software should adapt to businesses — not the other way around.
            </p>

            <p>
                Too many systems force rigid rules, unnecessary hardware, or constant monitoring.<br>
                That’s not how most businesses actually work.
            </p>

            <p>
                SharpFleet was built to give business owners control.<br>
                You decide what needs to be tracked.<br>
                You decide how strict the rules are.
            </p>

            <p>
                The system supports you — it doesn’t police your drivers.
            </p>

            <p>
                There was no grand plan.<br>
                No pitch deck.<br>
                No “startup idea”.
            </p>

            <p>
                Just a real problem, solved properly.
            </p>

            <p class="sf-about-close">
                Today, SharpFleet helps businesses keep accurate records without the stress.<br>
                Clear, flexible, and built for real-world work.
            </p>

            <p class="sf-about-close">
                Set the rules that make sense for your business —<br>
                and let your drivers get on with the job 🚗
            </p>

        </div>
    </div>
</div>

@endsection
