<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-2SCQ2YCEW8"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-2SCQ2YCEW8');
        </script>
        
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync | Testimonials')</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/testimonials.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('/favicon.ico') }}">
</head>

<body class="testimonials-body">

<header class="tl-header">
    <div class="logo">
        <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo">
    </div>
    <button class="hamburger" onclick="toggleMenu()" aria-label="Open navigation menu">☰</button>
</header>

<div id="overlayMenu" class="overlay-menu">
    <button class="close-menu" onclick="toggleMenu()">×</button>
    <ul>
            @foreach(($menuItems ?? []) as $item)
                <li>
                    <a 
                        href="{{ $item->url }}"
                        onclick="toggleMenu()"
                        @if($item->open_in_new_tab) target="_blank" @endif
                    >
                        {{ $item->label }}
                    </a>
                </li>
            @endforeach
            </ul>
</div>

<main class="tl-main">
    @yield('content')
</main>

<footer class="cp-footer">
    © 2025 SharpLync Pty Ltd · All rights reserved · Straightforward Support, <span class="cp-hl">Modern Results</span>
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