<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-2SCQ2YCEW8"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-2SCQ2YCEW8');
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync | IT Support & Cloud Services')</title>

    <meta name="description" content="SharpLync delivers reliable IT support, cloud services, and technology solutions across the Granite Belt and beyond. Straightforward Support, modern results.">
    <meta name="keywords" content="SharpLync, IT Support, Cloud Services, Managed IT, Granite Belt, Warwick, Stanthorpe, Tenterfield">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://sharplync.com.au/">
    <link rel="sitemap" type="application/xml" title="Sitemap" href="/sitemap.xml">
    <meta name="author" content="SharpLync Pty Ltd">

    {{-- Structured data for Google / Knowledge Graph --}}
    @verbatim
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "SharpLync Pty Ltd",
      "url": "https://sharplync.com.au",
      "logo": "https://sharplync.com.au/images/sharplync-logo.png",
      "sameAs": [
        "https://www.linkedin.com/company/sharplync",
        "https://x.com/sharplync"
      ],
      "description": "SharpLync provides professional IT support, cloud solutions, and managed services with a personal touch."
    }
    </script>
    @endverbatim

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="{{ secure_asset('css/sharplync.css') }}">

    <!-- NEW HEADER STYLES (SPLIT NAV + SEARCH) -->
    <style>
        .main-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            height: 90px;
            position: sticky;
            top: 0;
            z-index: 1000;
            background: linear-gradient(135deg, rgba(10, 42, 77, 0.85) 0%, rgba(16, 73, 118, 0.65) 40%, rgba(44, 191, 174, 0.45) 100%);
            backdrop-filter: blur(6px);
        }

        .nav-left,
        .nav-center,
        .nav-right {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        /* Links */
        .nav-link {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.25s ease;
        }

        .nav-link:hover {
            color: #2CBFAE;
        }

        .nav-active {
            color: #2CBFAE !important;
        }

        /* Logo */
        .logo img {
            height: 42px;
        }

        /* Search Bar */
        .nav-search {
            position: relative;
            width: 360px;
            max-width: 100%;
        }

        .nav-search input {
            width: 100%;
            padding: 10px 14px 10px 40px;
            border-radius: 14px;
            border: none;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            color: white;
            font-size: 15px;
            outline: none;
            transition: box-shadow 0.25s ease;
        }

        .nav-search input:focus {
            box-shadow: 0 0 9px #2CBFAE;
        }

        .nav-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 15px;
            opacity: 0.7;
        }

        /* Hamburger */
        .hamburger {
            display: none;
            background: none;
            border: none;
            font-size: 32px;
            color: #fff;
            cursor: pointer;
        }

        @media (max-width: 980px) {

            .nav-left .nav-link,
            .nav-right .nav-link,
            .nav-center {
                display: none;
            }

            .hamburger {
                display: block;
            }
        }

        /* MOBILE NAV (formerly overlay-menu) */
        .mobile-nav {
            position: fixed;
            top: 0;
            right: 0;
            width: 0;
            height: 100%;
            background: rgba(10,42,77,0.92);
            backdrop-filter: blur(10px);
            overflow-x: hidden;
            transition: width 0.3s ease;
            z-index: 5000;
            padding-top: 60px;
        }

        .mobile-nav.show {
            width: 260px;
        }

        .mobile-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .mobile-nav a {
            display: block;
            padding: 16px 26px;
            font-size: 1.3rem;
            color: white;
            text-decoration: none;
        }

        .mobile-nav a:hover {
            color: #2CBFAE;
        }

        .close-mobile-nav {
            position: absolute;
            top: 10px;
            right: 22px;
            font-size: 38px;
            background: none;
            border: none;
            color: #2CBFAE;
            cursor: pointer;
        }
    </style>

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

        <!-- CENTER -->
        <div class="nav-center">
            <div class="nav-search">
                <span class="nav-search-icon">🔍</span>
                <input type="text" placeholder="Search SharpLync...">
            </div>
        </div>

        <!-- RIGHT -->
        <div class="nav-right">
            <a href="/about" class="nav-link {{ request()->is('about') ? 'nav-active' : '' }}">About</a>
            <a href="/testimonials" class="nav-link {{ request()->is('testimonials') ? 'nav-active' : '' }}">Testimonials</a>
            <a href="/contact" class="nav-link {{ request()->is('contact') ? 'nav-active' : '' }}">Contact</a>
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

            <li style="padding: 16px 26px;">
                <input type="text" placeholder="Search..." style="
                    width: 100%;
                    padding: 10px 12px;
                    border-radius: 10px;
                    border: none;
                    outline: none;">
            </li>

            <li><a href="/about">About</a></li>
            <li><a href="/testimonials">Testimonials</a></li>
            <li><a href="/contact">Contact</a></li>
            <li><a href="/login">Login</a></li>
        </ul>
    </div>

    <!-- ========================= ORIGINAL OVERLAY MENU (UNTOUCHED) ========================= -->
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

        // Existing overlay menu script
        function toggleMenu() {
            const overlay = document.getElementById('overlayMenu');
            overlay.classList.toggle('show');
            document.body.style.overflow = overlay.classList.contains('show') ? 'hidden' : 'auto';
        }

        // Fade in elements on scroll
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
