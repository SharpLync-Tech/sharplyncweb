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
            <a href="/register" class="nav-link {{ request()->is('contact') ? 'nav-active' : '' }}">Register</a>
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
            <li><a href="/register">Register</a></li>
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
