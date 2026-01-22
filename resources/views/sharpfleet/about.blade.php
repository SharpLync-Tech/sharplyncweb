@extends('layouts.sharpfleet')

@section('title', 'About SharpFleet')

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
                <span>About</span><br>
                <span>SharpFleet</span>
            </h1>
            <span class="sf-about-underline"></span>
        </header>

        {{-- =========================
             STORY
        ========================== --}}
        <div class="sf-about-story">

            <p>
                SharpFleet was built to solve a very real, very common problem:
                keeping accurate vehicle and trip records without slowing people down.
            </p>

            <p>
                For many businesses, logging trips sounds simple, until it isn’t.<br>
                Multiple vehicles, shared drivers, paper logbooks left in the wrong car,
                and details filled in days later (or not at all).
            </p>

            <p>
                What should be a quick task turns into a chore.<br>
                Trips are forgotten, records become unreliable, and compliance feels harder than it needs to be.
            </p>

            <p>
                SharpFleet takes a different approach.
            </p>

            <p>
                It’s designed to work where the work happens. On the driver’s phone.<br>
                Always available. Easy to use. No extra hardware.
            </p>

            <p>
                Trips can be logged in the moment, not guessed later.<br>
                Vehicles, fuel receipts, servicing, registration, and reminders live in one place,
                instead of being scattered across paperwork and spreadsheets.

            </p>

            <p>
                Over time, SharpFleet grew into a complete fleet logbook system,
                built around flexibility rather than rigid rules.
            </p>

            <p class="sf-about-key">
                Software should adapt to businesses, not the other way around.
            </p>

            <p>
                Too many fleet systems assume every business operates the same way.<br>
                Mandatory tracking, expensive hardware, or features that feel more like surveillance than support.
            </p>

            <p>
                SharpFleet gives you control.<br>
                You choose what to track.<br>
                You decide how strict the rules need to be.
            </p>

            <p>
                The system supports your business, it doesn’t get in the way.
            </p>

            <p>
                There was no grand roadmap or one-size-fits-all solution.<br>
                Just a focus on building something practical, reliable,
                and suited to real-world work.
            </p>

            <p class="sf-about-close">
                Today, SharpFleet helps businesses stay organised,
                compliant, and in control, without unnecessary complexity.
            </p>

            <p class="sf-about-close">
                Set the rules that make sense for your business,<br>
                and let your drivers get on with the job.
            </p>

        </div>
    </div>
</div>

@endsection
