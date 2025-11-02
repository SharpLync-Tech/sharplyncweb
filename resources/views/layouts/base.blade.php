<!-- 
  Page: base.blade.php
  Version: v2.1 (C2.1)
  Last updated: 03 Nov 2025 by Max (ChatGPT)
  Description: Added glassy overlay menu and larger logo with slight overlap.
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync')</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ secure_asset('css/sharplync.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('/favicon.ico') }}">
</head>
<body>

    <!-- ========================= HEADER ========================= -->
    <header class="main-header">
        <div class="logo">
            <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo">
        </div>
        <button class="hamburger" onclick="toggleMenu()" aria-label="Open navigation menu">☰</button>
    </header>

    <!-- ========================= OVERLAY MENU ========================= -->
    <div id="overlayMenu" class="overlay-menu" role="navigation" aria-label="Main menu">
        <button class="close-menu" onclick="toggleMenu()" aria-label="Close navigation menu">×</button>
        <ul>
            <li><a href="/">Home</a></li>
            <li><a href="#services" onclick="toggleMenu()">Services</a></li>
            <li><a href="#about" onclick="toggleMenu()">About Us</a></li>
            <li><a href="#contact" onclick="toggleMenu()">Contact Us</a></li>
        </ul>
    </div>

    <script>
    function toggleMenu() {
        const overlay = document.getElementById('overlayMenu');
        overlay.classList.toggle('show');
        document.body.style.overflow = overlay.classList.contains('show') ? 'hidden' : 'auto';
    }
    </script>

    <!-- ========================= MAIN CONTENT ========================= -->
    <main>
        @yield('content')
    </main>

    <!-- ========================= FOOTER ========================= -->
    <footer>
        <div class="footer-content">
            <p>&copy; {{ date('Y') }} SharpLync Pty Ltd. All rights reserved.</p>
            <div class="social-icons">
                <a href="#"><img src="{{ asset('images/linkedin.png') }}" alt="LinkedIn"></a>
                <a href="#"><img src="{{ asset('images/x.png') }}" alt="X (Twitter)"></a>
                <a href="#"><img src="{{ asset('images/email.png') }}" alt="Email"></a>
            </div>
        </div>
    </footer>

</body>
</html>