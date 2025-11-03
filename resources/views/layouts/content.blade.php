<!-- 
  Layout: content.blade.php
  Version: v1.2 (Verified)
  Description: Same as base.blade.php but isolated stylesheet for About/Services pages.
-->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'SharpLync | IT Support & Cloud Services')</title>

  <meta name="description" content="SharpLync delivers reliable IT support, cloud services, and technology solutions across the Granite Belt and beyond. Old school support, modern results.">
  <meta name="keywords" content="SharpLync, IT Support, Cloud Services, Managed IT, Granite Belt, Warwick, Stanthorpe, Tenterfield">
  <meta name="author" content="SharpLync Pty Ltd">

  {{-- ✅ JSON-LD --}}
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

  <!-- ✅ Allow per-page styles -->
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

  <!-- ========================= MENU ========================= -->
  <div id="overlayMenu" class="overlay-menu" role="navigation">
    <button class="close-menu" onclick="toggleMenu()">×</button>
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
        <a href="mailto:info@sharplync.com.au">
          <img src="{{ asset('images/email.png') }}" alt="Email">
        </a>
      </div>
    </div>
  </footer>

  <!-- ========================= JS ========================= -->
  <script>
    function toggleMenu() {
      const overlay = document.getElementById('overlayMenu');
      overlay.classList.toggle('show');
      document.body.style.overflow = overlay.classList.contains('show') ? 'hidden' : 'auto';
    }
  </script>

  @stack('scripts')
</body>
</html>