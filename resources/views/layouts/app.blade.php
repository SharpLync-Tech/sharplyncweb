<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.head')
    <title>@yield('title', 'SharpLync')</title>
</head>
<body>
<header>
    <img src="{{ asset('images/logo.png') }}" alt="SharpLync Logo" class="logo">
    <div class="hamburger" id="menuToggle" onclick="toggleMenu()">☰</div>

    <nav>
        <ul>
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">Projects</a></li>
            <li><a href="#">Reports</a></li>
        </ul>
    </nav>
</header>

<div class="mobile-nav" id="mobileNav">
    <button type="button" class="close-btn" onclick="toggleMenu()">✕</button>
    <a href="#">Dashboard</a>
    <a href="#">Projects</a>
    <a href="#">Reports</a>
</div>

<div class="overlay" id="overlay" onclick="toggleMenu()"></div>

<main>
    @yield('content')
</main>

<footer>
    &copy; {{ date('Y') }} SharpLync. All rights reserved.
</footer>

@include('layouts.footer')
</body>
</html>
