<!-- 
  Layout: content.blade.php
  Version: v1.0
  Last updated: 04 Nov 2025 by Jannie & Max
  Description: Identical to base layout but designed for content pages (About, Services, Contact).
               Uses its own CSS file (content-pages.css) to prevent home-page overrides.
-->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync | IT Support & Cloud Services')</title>

    <meta name="description" content="SharpLync delivers reliable IT support, cloud services, and technology solutions across the Granite Belt and beyond. Old school support, modern results.">
    <meta name="keywords" content="SharpLync, IT Support, Cloud Services, Managed IT, Granite Belt, Warwick, Stanthorpe, Tenterfield">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://sharplync.com.au/">
    <link rel="sitemap" type="application/xml" title="Sitemap" href="/sitemap.xml">
    <meta name="author" content="SharpLync Pty Ltd">

    {{-- ✅ Structured Data for Google --}}
    @verbatim
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "SharpLync Pty Ltd",
      "url": "https://sharplync.com.au",
      "logo": "https://sharplync.com.au/images/sharplync-logo.png",
      "sameAs": [
        "https://www.linkedin.com/company/sharplync",
        "https://x.com/sharplync"
      ],
      "description": "SharpLync provides professional IT support, cloud solutions, and managed services with a personal touch."
    }
    </script>
    @endverbatim

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <!-- ✅ Dedicated Content Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/pages/content-pages.css') }}">

    <!-- ✅ Allow extra per-page styles -->
    @stack('styles')

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
            <li><a href="/services" onclick="toggleMenu()">Services</a></li>
            <li><a href="/about" onclick="toggleMenu()">About Us</a></li>
            <li><a href="/contact" onclick="toggleMenu()">Contact Us</a></li>
        </ul>
    </div>

    <!-- ========================= MAIN ========================= -->
    <main>
        @yield('content')
    </main>

    <!-- ========================= FOOTER ========================= -->
    <footer>
        <div class="footer-content">
            <p>&copy; {{ date('Y') }} SharpLync Pty Ltd. All rights reserved.</p>
            <div class="social-icons">
                <a href="https://www.linkedin.com/company/sharplync">
                    <img src="{{ asset('images/linkedin.png') }}" alt="LinkedIn">
                </a>
                <!-- <a href="#"><img src="{{ asset('images/x.png') }}" alt="X (Twitter)"></a> -->
                <a href="mailto:info@sharplync.com.au">
                    <img src="{{ asset('images/email.png') }}" alt="Email">
                </a>
            </div>
        </div>
    </footer>

    <!-- ========================= SCRIPTS ========================= -->
    <script>
        // ✅ Toggle overlay menu
        function toggleMenu() {
            const overlay = document.getElementById('overlayMenu');
            overlay.classList.toggle('show');
            document.body.style.overflow = overlay.classList.contains('show') ? 'hidden' : 'auto';
        }

        // ✅ Fade-in animation on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) entry.target.classList.add('visible');
            });
        }, { threshold: 0.15 });

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.fade-section').forEach(section => observer.observe(section));
        });
    </script>

    <!-- ✅ Allow additional per-page scripts -->
    @stack('scripts')

</body>
</html>