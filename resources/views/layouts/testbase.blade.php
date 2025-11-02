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
        </div>
    </footer>
</body>
</html>