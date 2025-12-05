<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync | Testimonials')</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Only testimonials stylesheet (prevents global CSS bleed) -->
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

    <!-- ===========================================================
         MOBILE NAV (Exact HTML from global site)
    ============================================================ -->
    <div id="mobileNav" class="mobile-nav isolated-mobile-nav">
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
    

    <!-- ===========================================================
         MAIN CONTENT
    ============================================================ -->
    <main>
        @yield('content')
    </main>

    <!-- FOOTER (kept your smaller, clean one) --> 
     <footer>
        <div class="footer-content-testimonials">
            <p>&copy; {{ date('Y') }} SharpLync Pty Ltd. All rights reserved.</p>
            <p>  
                <span class="sl-builtby">Designed & built by SharpLync</span>
            </p>
            <div class="social-icons">
                <a href="https://www.linkedin.com/company/sharplync"><img src="{{ asset('images/linkedin.png') }}" alt="LinkedIn"></a>
                <a href="https://www.facebook.com/SharpLync"><img src="{{ asset('images/facebook.png') }}" alt="Facebook"></a>
                <a href="mailto:info@sharplync.com.au"><img src="{{ asset('images/email.png') }}" alt="Email"></a>
            </div>
        </div>
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
