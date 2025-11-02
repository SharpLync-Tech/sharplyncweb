<!-- 
  Page: base.blade.php
  Version: v1.8
  Last updated: 29 Oct 2025 by Max (ChatGPT)
  Description: Layout identical to v1.5/v1.7; no structural or functional changes.
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync')</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
   
    {{-- Load special stylesheet only for test-threatpulse page --}}
        @if (Request::is('test-threatpulse'))
                <link rel="stylesheet" href="{{ secure_asset('css/sharplync-test.css') }}">
            @else
                <link rel="stylesheet" href="{{ secure_asset('css/sharplync.css') }}">
        @endif

    <link rel="icon" type="image/x-icon" href="{{ asset('/favicon.ico') }}">
</head>
<body>

    <!-- ========================= HEADER ========================= -->
    <header class="main-header">
        <div class="logo">
            <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo" style="height: 50px;">
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