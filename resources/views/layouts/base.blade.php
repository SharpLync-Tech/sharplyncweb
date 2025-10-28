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

<header class="main-header">
    <div class="logo">
        <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo">
    </div>
    <nav>
        <ul>
            <li><a href="/">Home</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="#about">About Us</a></li>
            <li><a href="#contact">Contact Us</a></li>
        </ul>
    </nav>
</header>

<main>
    @yield('content')
</main>

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
