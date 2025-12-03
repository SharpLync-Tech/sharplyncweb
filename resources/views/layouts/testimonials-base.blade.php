<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync | Testimonials')</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- ONLY this stylesheet to avoid homepage/marketing CSS -->
    <link rel="stylesheet" href="{{ asset('css/testimonials.css') }}">

    <link rel="icon" type="image/x-icon" href="{{ asset('/favicon.ico') }}">
</head>

<body class="testimonials-body">

    <!-- NEW NAVBAR (Pure, isolated) -->
    <header class="tl-header">
        <div class="logo">
            <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo">
        </div>
        <button class="hamburger" onclick="toggleMenu()">☰</button>
    </header>

    <!-- OVERLAY MENU -->
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

    <main>
        @yield('content')
    </main>

    <!-- Optional footer -->
    <footer class="cp-footer">
        © 2025 SharpLync Pty Ltd · All rights reserved · Old School Support, <span class="cp-hl">Modern Results</span>
    </footer>

<script>
function toggleMenu() {
    const overlay = document.getElementById('overlayMenu');
    overlay.classList.toggle('show');
    document.body.style.overflow = overlay.classList.contains('show') ? 'hidden' : '';
}
</script>

@stack('scripts')

</body>
</html>
