<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync | Testimonials')</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Only testimonials stylesheet (prevents global CSS bleed) -->
     <link rel="stylesheet" href="{{ secure_asset('css/sharplync-nav.css') }}">
     <link rel="stylesheet" href="{{ asset('css/testimonials.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('/favicon.ico') }}">
</head>
<body class="testimonials-body">

    <!-- ===========================================================
         BRAND-NEW SHARPLYNC NAV (HTML ONLY — NO global CSS loaded)
    ============================================================ -->
    <header class="main-header isolated-header">

        <!-- LEFT -->
        <div class="nav-left">
            <a href="/" class="logo">
                <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo">
            </a>
            <a href="/" class="nav-link">Home</a>
            <a href="/services" class="nav-link">Services</a>
        </div>

        <!-- CENTER (Search Bar) -->
        <div class="nav-center">
            <div class="nav-search">
                <span class="nav-search-icon">🔍</span>
                <input type="text" placeholder="Search SharpLync...">
            </div>
        </div>

        <!-- RIGHT -->
        <div class="nav-right">
            <a href="/about" class="nav-link">About</a>
            <a href="/testimonials" class="nav-link nav-active">Testimonials</a>
            <a href="/contact" class="nav-link">Contact</a>
            <a href="/login" class="nav-link">Login</a>

            <button class="hamburger" onclick="toggleMobileNav()">☰</button>
        </div>

    </header>

    <!-- ===========================================================
         MOBILE NAV (Exact HTML from global site)
    ============================================================ -->
    <div id="mobileNav" class="mobile-nav isolated-mobile-nav">
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
            <li><a href="/testimonials" class="active">Testimonials</a></li>
            <li><a href="/contact">Contact</a></li>
            <li><a href="/login">Login</a></li>
        </ul>
    </div>

    <!-- ===========================================================
         TESTIMONIALS OVERLAY MENU (your version)
    ============================================================ -->
    <div id="overlayMenu" class="overlay-menu">
        <button class="close-menu" onclick="toggleMenu()">×</button>

        <ul>
            <li><a href="/" onclick="toggleMenu()">Home</a></li>
            <li><a href="/services" onclick="toggleMenu()">Services</a></li>
            <li><a href="/about" onclick="toggleMenu()">About</a></li>
            <li><a href="/testimonials" class="active" onclick="toggleMenu()">Testimonials</a></li>
            <li><a href="/contact" onclick="toggleMenu()">Contact</a></li>
            <li><a href="/login" onclick="toggleMenu()">Login</a></li>
        </ul>
    </div>

    <!-- ===========================================================
         MAIN CONTENT
    ============================================================ -->
    <main>
        @yield('content')
    </main>

    <!-- FOOTER (kept your smaller, clean one) -->
    <footer class="cp-footer">
        © 2025 SharpLync Pty Ltd · All rights reserved · Old School Support,
        <span class="cp-hl">Modern Results</span>
    </footer>


    <!-- ===========================================================
         SCRIPTS (local to testimonials only)
    ============================================================ -->
    <script>
        function toggleMobileNav() {
            document.getElementById('mobileNav').classList.toggle('show');
        }

        function toggleMenu() {
            const overlay = document.getElementById('overlayMenu');
            overlay.classList.toggle('show');
            document.body.style.overflow =
                overlay.classList.contains('show') ? 'hidden' : '';
        }
    </script>

    @stack('scripts')

</body>
</html>
