{{-- =========================================================
     SharpFleet - How It Works
     Layout-first scaffold (placeholder images)
========================================================= --}}

@extends('layouts.sharpfleet')

@section('title', 'How SharpFleet Works')

@section('sharpfleet-content')

<div class="sf-page sf-page--plain">

    {{-- ===============================
         Page Intro
    ================================ --}}
    <div class="sf-hero" id="top">
        <h1>How <span class="highlight">SharpFleet</span> Works</h1>

        <p class="sf-hero-intro">
            A quick look at how SharpFleet is used day to day, from drivers starting trips to clear, audit-ready reports.
        </p>
    </div>

    <nav class="sf-anchor-nav" aria-label="How it works sections">
        <ul class="sf-anchor-list">
            <li><a href="#trips">Start &amp; End Trips</a></li>
            <li><a href="#receipts">Receipt Capture</a></li>
            <li><a href="#offline">Works Offline</a></li>
            <li><a href="#reports">Reporting &amp; Compliance</a></li>
            <li><a href="#bookings">Bookings Overview</a></li>
        </ul>
    </nav>

    <a href="#top" class="sf-back-to-top" aria-label="Back to top">↑</a>

    {{-- ===============================
         Section: Start & End Trips
    ================================ --}}
    <section id="trips" class="sf-feature-row">
        <div class="sf-feature-image sf-feature-image--phone">
            {{--
            <iframe
                src="https://www.youtube.com/embed/JuDGt-_txZc"
                title="SharpFleet mobile start and end trips"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
            ></iframe>
            --}}
            <div class="sf-device-frame">
                <video
                    src="{{ asset('images/sharpfleet/startend.optimized.mp4') }}"
                    autoplay
                    loop
                    muted
                    playsinline
                ></video>
            </div>
        </div>
        <div class="sf-feature-text">
            <h2>Drivers start and end trips in seconds</h2>
            <p>
                Drivers record trips directly from their phone using a simple,
                guided flow designed to minimise missed or incomplete entries.
            </p>
        </div>
    </section>

    {{-- ===============================
         Section: Receipt Capture
    ================================ --}}
    <section id="receipts" class="sf-feature-row reverse">
        <div class="sf-feature-image sf-feature-image--phone">
            <img src="{{ asset('images/sharpfleet/logo.png') }}" alt="Receipt capture">
        </div>
        <div class="sf-feature-text">
            <h2>Capture receipts as you go</h2>
            <p>
                Drivers can attach receipts to trips at the time they occur,
                keeping supporting records linked and easy to find later.
            </p>
        </div>
    </section>

    {{-- ===============================
         Section: No GPS
    ================================ --}}
    <section id="offline" class="sf-feature-row">
        <div class="sf-feature-image sf-feature-image--phone">
            <img src="{{ asset('images/sharpfleet/logo.png') }}" alt="Offline mode">
        </div>
        <div class="sf-feature-text">
            <h2>Works even when offline</h2>
            <p>
                Trips can still be recorded without mobile coverage and will
                automatically sync once the device is back online.
            </p>
        </div>
    </section>

    {{-- ===============================
         Section: Reporting
    ================================ --}}
    <section id="reports" class="sf-feature-row">
        <div class="sf-feature-image">
            <img src="{{ asset('images/sharpfleet/logo.png') }}" alt="Reporting and compliance">
        </div>
        <div class="sf-feature-text">
            <h2>Clear reports for compliance and audits</h2>
            <p>
                Generate clean, easy-to-read reports covering vehicle usage,
                business vs private travel, and client-related trips.
            </p>
        </div>
    </section>

    {{-- ===============================
         Section: Bookings
    ================================ --}}
    <section id="bookings" class="sf-feature-row reverse">
        <div class="sf-feature-image">
            <img src="{{ asset('images/sharpfleet/logo.png') }}" alt="Bookings views">
        </div>
        <div class="sf-feature-text">
            <h2>Vehicle bookings at a glance</h2>
            <p>
                View bookings using day, week, or month layouts to understand
                availability and avoid clashes across shared vehicles.
            </p>
        </div>
    </section>

    {{-- ===============================
         CTA
    ================================ --}}
    <div class="sf-hero sf-hero-cta">
        <h2>Simple. Practical. Built for real businesses.</h2>
        <p>
            SharpFleet helps businesses stay organised without GPS tracking,
            hardware installs, or complicated workflows.
        </p>
        <a href="/app/sharpfleet/admin/register" class="sf-cta">
            Get Started
        </a>
    </div>

</div>

@endsection

@push('styles')
<style>
    .sf-page {
        display: flex;
        flex-direction: column;
        gap: 36px;
        padding: 8px 0 32px;
    }

    .sf-page--plain {
        background: #ffffff;
        padding: 16px 20px 40px;
        border-radius: 18px;
    }

    .sf-hero {
        padding: 8px 0;
    }

    .sf-hero h1,
    .sf-hero h2 {
        margin-bottom: 10px;
    }

    .sf-hero-intro {
        max-width: 720px;
        margin: 0;
    }

    .sf-anchor-nav {
        position: sticky;
        top: 86px;
        z-index: 2;
        background: rgba(255, 255, 255, 0.9);
        border-bottom: 1px solid rgba(10, 42, 77, 0.1);
        padding: 10px 0 14px;
        backdrop-filter: blur(6px);
    }

    .sf-anchor-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 16px;
        list-style: none;
        padding: 0;
        margin: 0;
        font-weight: 600;
    }

    .sf-anchor-list a {
        color: #0A2A4D;
        text-decoration: none;
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(44, 191, 174, 0.12);
    }

    .sf-anchor-list a:hover {
        background: rgba(44, 191, 174, 0.22);
    }

    .sf-back-to-top {
        position: fixed;
        right: 18px;
        bottom: 22px;
        width: 44px;
        height: 44px;
        border-radius: 999px;
        background: rgba(10, 42, 77, 0.9);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: 20px;
        box-shadow: 0 10px 20px rgba(10, 42, 77, 0.2);
        z-index: 10;
        transition: transform 0.15s ease, background 0.15s ease;
        opacity: 0;
        pointer-events: none;
        transform: translateY(6px);
    }

    .sf-back-to-top:hover {
        background: rgba(10, 42, 77, 1);
        transform: translateY(-2px);
    }

    .sf-back-to-top.is-visible {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0);
    }

    .sf-feature-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.1fr);
        gap: 24px;
        align-items: center;
        padding: 8px 0;
        scroll-margin-top: 120px;
    }

    .sf-feature-row + .sf-feature-row {
        border-top: 1px solid rgba(10, 42, 77, 0.08);
        padding-top: 28px;
        margin-top: 8px;
    }

    .sf-feature-row.reverse {
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
    }

    .sf-feature-row.reverse .sf-feature-image {
        order: 2;
    }

    .sf-feature-row.reverse .sf-feature-text {
        order: 1;
    }

    .sf-feature-image {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .sf-feature-image img {
        width: 100%;
        max-width: 360px;
        height: auto;
        border-radius: 12px;
        box-shadow: 0 8px 18px rgba(44, 191, 174, 0.18);
    }

    .sf-feature-image iframe {
        width: 100%;
        max-width: 360px;
        border: 0;
        border-radius: 12px;
        box-shadow: 0 8px 18px rgba(44, 191, 174, 0.18);
    }

    .sf-feature-image video {
        width: 100%;
        max-width: 360px;
        border-radius: 12px;
        box-shadow: 0 8px 18px rgba(44, 191, 174, 0.18);
    }

    .sf-device-frame {
        position: relative;
        width: 100%;
        max-width: 300px;
        aspect-ratio: 9 / 16;
        padding: 10px;
        border-radius: 26px;
        background: linear-gradient(160deg, rgba(10, 42, 77, 0.9), rgba(44, 191, 174, 0.35));
        box-shadow: 0 18px 36px rgba(10, 42, 77, 0.22);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .sf-device-frame::before {
        content: "";
        position: absolute;
        inset: 6px;
        border-radius: 22px;
        border: 1px solid rgba(255, 255, 255, 0.22);
        box-shadow: inset 0 0 0 1px rgba(10, 42, 77, 0.08);
        pointer-events: none;
    }

    .sf-device-frame::after {
        content: "";
        position: absolute;
        top: 12px;
        left: 50%;
        width: 64px;
        height: 6px;
        transform: translateX(-50%);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.4);
        box-shadow: inset 0 0 0 1px rgba(10, 42, 77, 0.25);
        pointer-events: none;
    }

    .sf-device-frame video {
        display: block;
        width: 100%;
        height: 100%;
        border-radius: 18px;
        object-fit: contain;
        background: #0a0f14;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.18);
    }

    .sf-feature-image--phone .sf-device-frame:hover {
        transform: translateY(-4px);
        box-shadow: 0 22px 44px rgba(10, 42, 77, 0.28);
    }

    .sf-feature-image--phone img {
        max-width: 300px;
        aspect-ratio: 9 / 16;
        height: auto;
        object-fit: cover;
    }

    .sf-feature-image--phone iframe {
        max-width: 300px;
        aspect-ratio: 9 / 16;
        height: auto;
    }

    .sf-feature-image--phone .sf-device-frame {
        max-width: 300px;
    }

    .sf-feature-text h2 {
        margin-bottom: 10px;
    }

    .sf-feature-text p {
        margin: 0;
    }

    .sf-hero-cta {
        text-align: center;
    }

    @media (max-width: 900px) {
        .sf-anchor-nav {
            top: 72px;
        }

        .sf-feature-row,
        .sf-feature-row.reverse {
            grid-template-columns: 1fr;
        }

        .sf-feature-row.reverse .sf-feature-image,
        .sf-feature-row.reverse .sf-feature-text {
            order: initial;
        }

        .sf-feature-image img {
            max-width: 100%;
        }

        .sf-device-frame {
            max-width: 100%;
            padding: 8px;
            border-radius: 22px;
            box-shadow: 0 12px 24px rgba(10, 42, 77, 0.18);
            aspect-ratio: auto;
            height: auto;
            overflow: hidden;
        }

        .sf-device-frame::before {
            inset: 5px;
            border-radius: 18px;
        }

        .sf-device-frame::after {
            top: 10px;
            width: 52px;
            height: 5px;
        }

        .sf-device-frame video {
            height: auto;
            aspect-ratio: 9 / 16;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (() => {
        const btn = document.querySelector('.sf-back-to-top');
        if (!btn) return;

        const toggle = () => {
            const show = window.scrollY > 240;
            btn.classList.toggle('is-visible', show);
        };

        toggle();
        window.addEventListener('scroll', toggle, { passive: true });
    })();
</script>
@endpush
