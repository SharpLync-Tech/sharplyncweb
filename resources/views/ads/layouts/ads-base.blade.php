{{-- 
  Layout: ads/layouts/ads-base.blade.php
  Purpose: Base layout for all Google Ads landing pages (isolated)
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'SharpLync | Business IT Support')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- IMPORTANT: keep ads pages hidden until we’re ready --}}
    <meta name="robots" content="noindex, nofollow">

    {{-- Optional: separate favicon for ads if you want, otherwise reuse main --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('/favicon.ico') }}">

    {{-- Fonts (local to ads) --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- Isolated ads stylesheet --}}
    <link rel="stylesheet" href="{{ asset('css/ads/ads-base.css') }}">
</head>
<body class="ads-body">

    {{-- ===========================================
         STICKY HEADER (Logo link + mobile centering)
    ============================================ --}}
    <header class="ads-header">
        <div class="ads-header-inner">
            <div class="ads-logo-wrap">
                <a href="{{ url('/') }}" class="ads-logo-link">
                    <img src="{{ asset('images/sharplync-logo.png') }}" 
                         alt="SharpLync Logo"
                         class="ads-logo">
                </a>
            </div>

            <div class="ads-header-tagline">
                <span>Business IT Support • Cybersecurity • Remote Help</span>
            </div>
        </div>
    </header>

    <main class="ads-main">
        @yield('content')
    </main>

    <footer class="ads-footer">
        <div class="ads-footer-inner">
            <p>&copy; {{ date('Y') }} SharpLync Pty Ltd. All rights reserved.</p>
            <p>Business IT Support • Cybersecurity • Remote Assistance</p>
            <p>Call: <a href="tel:0492014463">0492 014 463</a> • Email: <a href="mailto:support@sharplync.com.au">support@sharplync.com.au</a></p>
            <p class="ads-footer-built">Site designed &amp; built by SharpLync.</p>
        </div>
    </footer>

    {{-- Sticky header visual state --}}
    <script>
        document.addEventListener("scroll", () => {
            const header = document.querySelector(".ads-header");
            if (window.scrollY > 10) {
                header.classList.add("stuck");
            } else {
                header.classList.remove("stuck");
            }
        });
    </script>

</body>
</html>
