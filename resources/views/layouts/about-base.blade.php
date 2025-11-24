<!-- =====================
     Works on Mobile. Hamburger not working.
     ===================== -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync | About')</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/about.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('/favicon.ico') }}">

    <!-- ============================================== -->
    <!-- FORCE OVERLAY MENU HIDDEN (fix hamburger issue) -->
    <!-- ============================================== -->
    <style>
        #overlayMenu {
            display: none !important;
        }
        #overlayMenu.show {
            display: flex !important;
        }
    </style>
</head>

<body class="about-body">

<header class="about-header">
    <div class="logo">
        <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo">
    </div>
    <button class="hamburger" onclick="toggleMenu()" aria-label="Open navigation menu">☰</button>
</header>

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

<main class="about-main">
    {{-- ============================= --}}
{{-- MAIN SITE NAVIGATION BAR      --}}
{{-- (Restored exactly as before)  --}}
{{-- ============================= --}}

<header class="main-header">
    <div class="nav-container">

        {{-- Logo --}}
        <a href="/" class="nav-logo">
            <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo">
        </a>

        {{-- Hamburger Menu --}}
        <div class="hamburger" onclick="toggleMenu()">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>

        {{-- Desktop Nav --}}
        <nav class="nav-links">
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/services">Services</a></li>
                <li><a href="/login">Login</a></li>
                <li><a href="/register">Register</a></li>
                <li><a href="/about">About Us</a></li>
                <li><a href="/testimonials">Testimonials</a></li>
                <li><a href="/contact">Contact Us</a></li>
            </ul>
        </nav>

    </div>

    {{-- Mobile Slide-Out Menu --}}
    <div id="mobileMenu" class="mobile-menu">
        <ul>
            <li><a href="/" onclick="toggleMenu()">Home</a></li>
            <li><a href="/services" onclick="toggleMenu()">Services</a></li>
            <li><a href="/login" onclick="toggleMenu()">Login</a></li>
            <li><a href="/register" onclick="toggleMenu()">Register</a></li>
            <li><a href="/about" onclick="toggleMenu()">About Us</a></li>
            <li><a href="/testimonials" onclick="toggleMenu()">Testimonials</a></li>
            <li><a href="/contact" onclick="toggleMenu()">Contact Us</a></li>
        </ul>
    </div>
</header>

{{-- Mobile Menu Toggle Script --}}
<script>
function toggleMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('open');
}
</script>

    @yield('content')
</main>

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

<script>
    function toggleMenu() {
        const overlay = document.getElementById('overlayMenu');
        overlay.classList.toggle('show');
        document.body.style.overflow = overlay.classList.contains('show') ? 'hidden' : 'auto';
    }
</script>

@stack('scripts')
</body>
</html>
