<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google tag (GA4) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-2SCQ2YCEW8"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-2SCQ2YCEW8');
    </script>

    <!-- Google Ads tag (AW-17789891633) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17789891633"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'AW-17789891633');
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync | IT Support & Cloud Services for Queensland Businesses')</title>

    <!-- Primary SEO -->
    <meta name="description" content="SharpLync provides expert IT support, cybersecurity, and cloud services for businesses across Queensland. Proudly serving Stanthorpe and the Granite Belt with modern, reliable technology solutions.">
    <meta name="keywords" content="IT Support Queensland, IT Support Stanthorpe, Managed Services Queensland, Cloud Services QLD, Cybersecurity Granite Belt, Business IT Support, SharpLync">
    <meta name="robots" content="index, follow">
    <meta name="facebook-domain-verification" content="nlot90unrp0fw2s4uquw1q8hnxnh7a" />

    <!-- Canonical -->
    <link rel="canonical" href="https://sharplync.com.au/">

    <!-- Sitemap reference -->
    <link rel="sitemap" type="application/xml" title="Sitemap" href="/sitemap.xml">

    <meta name="author" content="SharpLync Pty Ltd">

    {{-- Structured data for Google / Knowledge Graph --}}
    @verbatim
    <script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "LocalBusiness",
    "name": "SharpLync Pty Ltd",
    "image": "https://sharplync.com.au/images/sharplync-logo.png",
    "@id": "https://sharplync.com.au",
    "url": "https://sharplync.com.au",
    "telephone": "+61 492 014 463",
    "address": {
        "@type": "PostalAddress",
        "addressLocality": "Stanthorpe",
        "addressRegion": "QLD",
        "addressCountry": "AU"
    },
    "description": "SharpLync provides IT support, managed services, cloud solutions, and cybersecurity for businesses across Queensland.",
    "sameAs": [
        "https://www.linkedin.com/company/sharplync",
        "https://x.com/sharplync"
    ]
    }
    </script>
    @endverbatim

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="{{ secure_asset('css/sharplync.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('css/sharplync-nav.css') }}">

    @stack('styles')
    <link rel="icon" type="image/x-icon" href="{{ asset('/favicon.ico') }}">
</head>

<body>

    <!-- ========================= HEADER ========================= -->
    <header class="main-header">

        <!-- LEFT -->
        <div class="nav-left">
            <a href="/" class="logo">
                <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo">
            </a>
            <a href="/" class="nav-link {{ request()->is('/') ? 'nav-active' : '' }}">Home</a>
            <a href="/services" class="nav-link {{ request()->is('services') ? 'nav-active' : '' }}">Services</a>
        </div>

        <!-- RIGHT -->
        <div class="nav-right">
            <a href="/about" class="nav-link {{ request()->is('about') ? 'nav-active' : '' }}">About</a>
            <a href="/testimonials" class="nav-link {{ request()->is('testimonials') ? 'nav-active' : '' }}">Testimonials</a>
            <a href="/contact" class="nav-link {{ request()->is('contact') ? 'nav-active' : '' }}">Contact</a>
            <a href="/policies/hub" class="nav-link {{ request()->is('policies') ? 'nav-active' : '' }}">Policies</a>
            <a href="/register" class="nav-link {{ request()->is('register') ? 'nav-active' : '' }}">Register</a>
            <a href="/login" class="nav-link {{ request()->is('login') ? 'nav-active' : '' }}">Login</a>

            <button class="hamburger" onclick="toggleMobileNav()">☰</button>
        </div>

    </header>

    <!-- ========================= MOBILE NAV ========================= -->
    <div id="mobileNav" class="mobile-nav">
        <button class="close-mobile-nav" onclick="toggleMobileNav()">×</button>

        <ul>
            <li><a href="/">Home</a></li>
            <li><a href="/services">Services</a></li>
            <li><a href="/about">About</a></li>
            <li><a href="/testimonials">Testimonials</a></li>
            <li><a href="/contact">Contact</a></li>
            <li><a href="/policies/hub">Policies</a></li>
            <li><a href="/register">Register</a></li>
            <li><a href="/login">Login</a></li>
        </ul>
    </div>

    <!-- ========================= ORIGINAL OVERLAY MENU ========================= -->
    <div id="overlayMenu" class="overlay-menu" role="navigation">
        <button class="close-menu" onclick="toggleMenu()">×</button>

        <ul>
            @foreach(($menuItems ?? []) as $item)
                <li>
                    <a href="{{ $item->url }}" onclick="toggleMenu()" @if($item->open_in_new_tab) target="_blank" @endif>
                        {{ $item->label }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- ========================= MAIN ========================= -->
    <main>
        @yield('content')
    </main>

    <!-- ========================= FOOTER ========================= -->
    <footer>
        <div class="footer-content">
            <p>&copy; {{ date('Y') }} SharpLync Pty Ltd. All rights reserved.</p>
            <p><span class="sl-builtby">Designed & built by SharpLync</span></p>

            <div class="social-icons">
                <a href="https://www.linkedin.com/company/sharplync"><img src="{{ asset('images/linkedin.png') }}" alt="LinkedIn"></a>
                <a href="https://www.facebook.com/SharpLync"><img src="{{ asset('images/facebook.png') }}" alt="Facebook"></a>
                <a href="mailto:info@sharplync.com.au"><img src="{{ asset('images/email.png') }}" alt="Email"></a>
            </div>
        </div>
    </footer>

    <!-- ========================= SCRIPTS ========================= -->
    <script>
        function toggleMobileNav() {
            document.getElementById('mobileNav').classList.toggle('show');
        }

        function toggleMenu() {
            const overlay = document.getElementById('overlayMenu');
            overlay.classList.toggle('show');
            document.body.style.overflow = overlay.classList.contains('show') ? 'hidden' : 'auto';
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) entry.target.classList.add('visible');
            });
        }, { threshold: 0.15 });

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.fade-section').forEach(section => observer.observe(section));
        });
    </script>

    @stack('scripts')

</body>
</html>
