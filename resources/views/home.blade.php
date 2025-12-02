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

    <!-- Header styles for new nav -->
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

        /* NAV LAYOUT (DESKTOP) */
        .nav-left,
        .nav-center,
        .nav-right {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-left a,
        .nav-right a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.25s ease;
        }

        .nav-left a:hover,
        .nav-right a:hover {
            color: #2CBFAE;
        }

        /* Active link */
        .nav-active {
            color: #2CBFAE !important;
        }

        /* SEARCH BAR */
        .nav-search {
            position: relative;
            width: 360px;
        }

        .nav-search input {
            width: 100%;
            padding: 10px 14px 10px 40px;
            border-radius: 12px;
            border: none;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(10px);
            color: white;
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

        .nav-search i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: rgba(255,255,255,0.7);
        }

        /* LOGO */
        .logo img {
            height: 42px;
        }

        /* MOBILE */
        .hamburger {
            display: none;
            background: none;
            border: none;
            font-size: 32px;
            color: white;
        }

        @media (max-width: 980px) {

            .nav-left a,
            .nav-right a,
            .nav-search {
                display: none;
            }

            .hamburger {
                display: block;
            }

            .main-header {
                justify-content: space-between;
            }
        }

        /* OVERLAY MENU (MOBILE) */
        .overlay-menu {
            position: fixed;
            top: 0;
            right: 0;
            height: 100%;
            width: 0;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(8px);
            overflow-x: hidden;
            transition: 0.3s;
            padding-top: 60px;
            z-index: 9999;
        }

        .overlay-menu.show {
            width: 260px;
        }

        .overlay-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .overlay-menu a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 14px 26px;
            font-size: 18px;
            transition: 0.25s;
        }

        .overlay-menu a:hover {
            color: #2CBFAE;
        }

        .close-menu {
            position: absolute;
            top: 12px;
            right: 20px;
            background: none;
            border: none;
            font-size: 44px;
            color: white;
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
            <a href="{{ url('/') }}" class="logo">
                <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo">
            </a>
            <a href="/" class="{{ request()->is('/') ? 'nav-active' : '' }}">Home</a>
            <a href="/services" class="{{ request()->is('services') ? 'nav-active' : '' }}">Services</a>
        </div>

        <!-- CENTER -->
        <div class="nav-center">
            <div class="nav-search">
                <i class="fa fa-search"></i>
                <input type="text" placeholder="Search SharpLync...">
            </div>
        </div>

        <!-- RIGHT -->
        <div class="nav-right">
            <a href="/about" class="{{ request()->is('about') ? 'nav-active' : '' }}">About</a>
            <a href="/testimonials" class="{{ request()->is('testimonials') ? 'nav-active' : '' }}">Testimonials</a>
            <a href="/contact" class="{{ request()->is('contact') ? 'nav-active' : '' }}">Contact</a>
            <a href="/login" class="{{ request()->is('login') ? 'nav-active' : '' }}">Login</a>

            <button class="hamburger" onclick="toggleMenu()" aria-label="Open navigation menu">☰</button>
        </div>
    </header>

    <!-- ========================= MOBILE OVERLAY MENU ========================= -->
    <div id="overlayMenu" class="overlay-menu" role="navigation" aria-label="Main menu">
        <button class="close-menu" onclick="toggleMenu()" aria-label="Close navigation menu">×</button>

        <ul>
            <li><a href="/">Home</a></li>
            <li><a href="/services">Services</a></li>

            <!-- Search for mobile -->
            <li style="padding: 14px 26px;">
                <input type="text" placeholder="Search..." style="
                    width: 100%;
                    padding: 10px;
                    border-radius: 8px;
                    border: none;
                    outline: none;
                    margin-top: 10px;">
            </li>

            <li><a href="/about">About</a></li>
            <li><a href="/testimonials">Testimonials</a></li>
            <li><a href="/contact">Contact</a></li>
            <li><a href="/login">Login</a></li>
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
        function toggleMenu() {
            const overlay = document.getElementById('overlayMenu');
            overlay.classList.toggle('show');
            document.body.style.overflow = overlay.classList.contains('show') ? 'hidden' : 'auto';
        }
    </script>
    @stack('scripts')

    <!-- FontAwesome (for search icon) -->
    <script src="https://kit.fontawesome.com/a2e0e9c6f4.js" crossorigin="anonymous"></script>

</body>
</html>
