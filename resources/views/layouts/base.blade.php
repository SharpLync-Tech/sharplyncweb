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

    <!-- New header / nav styles (split nav + search) -->
    <style>
        .main-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            height: 90px;
            position: relative;
            z-index: 50;
        }

        .nav-left,
        .nav-center,
        .nav-right {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .logo img {
            height: 42px;
        }

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

        /* Search bar */
        .nav-search {
            position: relative;
            width: 360px;
            max-width: 100%;
        }

        .nav-search input {
            width: 100%;
            padding: 10px 14px 10px 40px;
            border-radius: 12px;
            border: none;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(10px);
            color: #fff;
            font-size: 15px;
            outline: none;
            transition: box-shadow 0.25s ease;
        }

        .nav-search input::placeholder {
            color: rgba(255,255,255,0.6);
        }

        .nav-search input:focus {
            box-shadow: 0 0 8px #2CBFAE;
        }

        .nav-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: rgba(255,255,255,0.7);
        }

        /* Hamburger default hidden on desktop, shown on mobile via existing CSS + this backup */
        .hamburger {
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

            .main-header {
                padding: 0 1rem;
            }
        }
    </style>

    <!-- Additional page-specific styles -->
    @stack('styles')

    <link rel="icon" type="image/x-icon" href="{{ asset('/favicon.ico') }}">
    <!-- ============================================= -->
    <!-- LOGIN-TIME 2FA MODAL CSS (SharpLync WOW v2.0) -->
    <!-- ============================================= -->
</head>

<body>
    <!-- ========================= HEADER ========================= -->
    <header class="main-header">
        <!-- LEFT -->
        <div class="nav-left">
            <a href="{{ url('/') }}" class="logo">
                <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo">
            </a>
            <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'nav-active' : '' }}">Home</a>
            <a href="{{ url('/services') }}" class="nav-link {{ request()->is('services') ? 'nav-active' : '' }}">Services</a>
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
            <a href="{{ url('/about') }}" class="nav-link {{ request()->is('about') ? 'nav-active' : '' }}">About</a>
            <a href="{{ url('/testimonials') }}" class="nav-link {{ request()->is('testimonials') ? 'nav-active' : '' }}">Testimonials</a>
            <a href="{{ url('/contact') }}" class="nav-link {{ request()->is('contact') ? 'nav-active' : '' }}">Contact</a>
            <a href="{{ url('/login') }}" class="nav-link {{ request()->is('login') ? 'nav-active' : '' }}">Login</a>

            <button class="hamburger" onclick="toggleMenu()" aria-label="Open navigation menu">☰</button>
        </div>
    </header>

    <!-- ========================= OVERLAY MENU ========================= -->
    <div id="overlayMenu" class="overlay-menu" role="navigation" aria-label="Main menu">
        <button class="close-menu" onclick="toggleMenu()" aria-label="Close navigation menu">×</button>
        <ul>
            @foreach(($menuItems ?? []) as $item)
                <li>
                    <a 
                        href="{{ $item->url }}"
                        onclick="toggleMenu()"
                        @if($item->open_in_new_tab) target="_blank" @endif
                    >
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
        // Toggle overlay menu
        function toggleMenu() {
            const overlay = document.getElementById('overlayMenu');
            overlay.classList.toggle('show');
            document.body.style.overflow = overlay.classList.contains('show') ? 'hidden' : 'auto';
        }

        // Fade-in on scroll
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
