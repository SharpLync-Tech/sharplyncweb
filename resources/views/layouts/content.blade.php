<!-- 
  Layout: content.blade.php
  Version: v2.0
  Last updated: 04 Nov 2025 by Jannie & Max
  Description: Dedicated layout for content pages (About, Services, Contact) 
               identical to home page style but isolated from base.blade.php.
-->

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
  <title>@yield('title', 'SharpLync | IT Support & Cloud Services')</title>

  <meta name="description" content="SharpLync delivers reliable IT support, cloud services, and technology solutions across the Granite Belt and beyond. Old school support, modern results.">
  <meta name="keywords" content="SharpLync, IT Support, Cloud Services, Managed IT, Granite Belt, Warwick, Stanthorpe, Tenterfield">
  <meta name="author" content="SharpLync Pty Ltd">

  {{-- ✅ Structured Data --}}
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
    ]
  }
  </script>
  @endverbatim

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <!-- ✅ Dedicated Content Stylesheet -->
  <link rel="stylesheet" href="{{ secure_asset('css/pages/content-pages.css') }}">

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

  <!-- ========================= MAIN ========================= -->
  <main>
    @yield('content')
  </main>

  <!-- ========================= FOOTER ========================= -->
  <footer>
    <div class="footer-content">
      <p>&copy; {{ date('Y') }} SharpLync Pty Ltd. All rights reserved.</p>
      <div class="social-icons">
        <a href="https://www.linkedin.com/company/sharplync"><img src="{{ asset('images/linkedin.png') }}" alt="LinkedIn">
        </a>
        <a href="https://www.facebook.com/SharpLync"><img src="{{ asset('images/facebook.png') }}" alt="Facebook">
        </a>
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

    // ✅ Fade-in animation
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) entry.target.classList.add('visible');
      });
    }, { threshold: 0.15 });

    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.fade-section').forEach(section => observer.observe(section));
    });
  </script>

  @stack('scripts')
</body>
</html>