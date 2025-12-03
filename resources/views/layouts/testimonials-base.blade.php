<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync | Testimonials')</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Testimonials stylesheet only -->
    <link rel="stylesheet" href="{{ asset('css/testimonials.css') }}">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('/favicon.ico') }}">
</head>

<body class="testimonials-body">

    <!-- TOP NAVBAR (Same as new global nav) -->
    <header class="global-header">
        <div class="nav-container">
            <a href="/" class="nav-logo">
                <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo">
            </a>

            <!-- Full Nav (desktop) -->
            <nav class="nav-links">
                <a href="/">Home</a>
                <a href="/services">Services</a>
                <a href="/about">About</a>
                <a href="/testimonials" class="active">Testimonials</a>
                <a href="/contact">Contact</a>
                <a href="/login" class="nav-login">Login</a>
            </nav>

            <!-- Mobile Hamburger -->
            <button class="hamburger-btn" onclick="toggleMenu()">☰</button>
        </div>
    </header>

    <!-- MOBILE OVERLAY MENU -->
    <div id="mobileMenu" class="mobile-overlay">
        <button class="close-btn" onclick="toggleMenu()">×</button>

        <div class="overlay-links">
            <a href="/">Home</a>
            <a href="/services">Services</a>
            <a href="/about">About</a>
            <a href="/testimonials" class="active">Testimonials</a>
            <a href="/contact">Contact</a>
            <a href="/login">Login</a>
        </div>
    </div>

    <!-- MAIN PAGE CONTENT -->
    <main>
        @yield('content')
    </main>

    <!-- NO GLOBAL FOOTER HERE (prevents Blue Tetris Doom) -->

<script>
function toggleMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('show');
    document.body.style.overflow = menu.classList.contains('show') ? 'hidden' : '';
}
</script>

@stack('scripts')

</body>
</html>
