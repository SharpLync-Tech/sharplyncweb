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
        <li><a href="/">Home</a></li>
        <li><a href="/login" onclick="toggleMenu()">Login</a></li>
        <li><a href="/register" onclick="toggleMenu()">Register</a></li>
        <li><a href="/about" onclick="toggleMenu()">About Us</a></li>
        <li><a href="/testimonials" onclick="toggleMenu()">Testimonials</a></li>
        <li><a href="#contact" onclick="toggleMenu()">Contact Us</a></li>
    </ul>
</div>

<main class="about-main">
    @yield('content')
</main>

<footer class="about-footer">
    <div class="footer-content">
        <p>&copy; {{ date('Y') }} SharpLync Pty Ltd. All rights reserved.</p>
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
