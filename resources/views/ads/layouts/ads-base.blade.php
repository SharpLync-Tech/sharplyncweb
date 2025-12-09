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

    {{-- Google Analytics (GA4) --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-2SCQ2YCEW8"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-2SCQ2YCEW8');
    </script>

    {{-- Google Ads Global Site Tag --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17789891633"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'AW-17789891633');
    </script>

    {{-- IMPORTANT: Ads landing pages should not be indexed --}}
    <meta name="robots" content="noindex, nofollow">

    <link rel="icon" type="image/x-icon" href="{{ asset('/favicon.ico') }}">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/ads/ads-base.css') }}">
</head>

<body class="ads-body">

    <header class="ads-header">
        <div class="ads-header-inner">
            <div class="ads-logo-wrap">
                <a href="{{ url('/') }}" class="ads-logo-link">
                    <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo" class="ads-logo">
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
