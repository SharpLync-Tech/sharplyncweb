<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync | About')</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('/favicon.ico') }}">

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
