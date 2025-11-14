<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync | About')</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/about.css') }}">

    <link rel="icon" type="image/x-icon" href="{{ asset('/favicon.ico') }}">
</head>

<body class="about-body">

<header class="about-header">
    <div class="logo">
        <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo">
    </div>
    <button class="hamburger" onclick="toggleMenu()">☰</button>
</header>

<div id="overlayMenu" class="overlay-menu">
    <button class="close-menu" onclick="toggleMenu()">×</button>
    <ul>
        <li><a href="/">Home</a></li>
        <li><a href="/login">Login</a></li>
        <li><a href="/register">Register</a></li>
        <li><a href="/about">About Us</a></li>
        <li><a href="/testimonials">Testimonials</a></li>
        <li><a href="#contact">Contact Us</a></li>
    </ul>
</div>

<main class="about-main">
    @yield('content')
</main>

<footer class="about-footer">
    <p>&copy; {{ date('Y') }} SharpLync Pty Ltd. All rights reserved.</p>
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
