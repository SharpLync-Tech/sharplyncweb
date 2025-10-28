<!-- 
  Page: base.blade.php
  Version: v1.2
  Last updated: 29 Oct 2025 by Max (ChatGPT)
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync')</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ secure_asset('css/sharplync.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
</head>
<body>

    <!-- ========================= HEADER ========================= -->
    <header class="main-header">
        <div class="logo">
            <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo">
        </div>

        <!-- Hamburger menu (mobile only) -->
        <button class="hamburger" onclick="toggleMenu()">☰</button>

        <nav>
            <ul id="navLinks">
                <li><a href="/">Home</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#about">About Us</a></li>
                <li><a href="#contact">Contact Us</a></li>
            </ul>
        </nav>
    </header>    

    <script>
    function toggleMenu() {
        const navLinks = document.getElementById('navLinks');
        navLinks.classList.toggle('show');
    }
    </script>

    <!-- ========================= MAIN ========================= -->
    <main>
        @yield('content')
    </main>

    <!-- ========================= FOOTER ========================= -->
    <footer>
        <div class="footer-content">
            <p>&copy; {{ date('Y') }} SharpLync Pty Ltd. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>