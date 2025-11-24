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

    {{-- Structured data for Google / Knowledge Graph --}}
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

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="{{ secure_asset('css/sharplync.css') }}">
    <!-- Additional page-specific styles -->
    @stack('styles')

    <link rel="icon" type="image/x-icon" href="{{ asset('/favicon.ico') }}">
    <!-- ============================================= -->
<!-- LOGIN-TIME 2FA MODAL CSS (SharpLync WOW v2.0) -->
<!-- ============================================= -->
<style>
    /* Backdrop */
    .cp-modal-backdrop {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.60);
        backdrop-filter: blur(4px);
        z-index: 9999;
    }

    .cp-modal-backdrop.cp-modal-visible {
        display: flex !important;
    }

    /* Modal sheet */
    .cp-modal-sheet {
        background: #0A2A4D; /* SharpLync Navy */
        border: 2px solid #2CBFAE; /* thin teal border */
        border-radius: 18px;
        padding: 2rem 2rem 2rem;
        width: 94%;
        max-width: 500px;

        box-shadow:
            0 0 15px rgba(44, 191, 174, 0.25),
            0 20px 60px rgba(0,0,0,0.45);

        animation: modalPop .25s ease-out;
        color: white;
    }

    @keyframes modalPop {
        0%   { transform: translateY(25px) scale(.97); opacity: 0; }
        100% { transform: translateY(0) scale(1); opacity: 1; }
    }

    /* Header */
    .cp-modal-header h3 {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 600;
        color: #ffffff;
    }

    .cp-modal-subtitle {
        font-size: .9rem;
        color: #cfe7ef;
    }

    /* Close button */
    .cp-modal-close {
        background: transparent;
        border: 1px solid rgba(44,191,174,0.5);
        color: #2CBFAE;
        font-size: 1.2rem;
        border-radius: 6px;
        padding: 2px 7px;
        cursor: pointer;
        transition: 0.2s ease;
    }
    .cp-modal-close:hover {
        background: rgba(44,191,174,0.15);
        border-color: #2CBFAE;
        color: #fff;
    }

    /* OTP DIGIT BOXES */
    .login-2fa-digit {
        width: 50px !important;
        height: 58px !important;
        text-align: center;
        font-size: 1.7rem !important;
        font-weight: 600;
        border-radius: 10px;
        border: 2px solid rgba(44, 191, 174, 0.6);
        background: rgba(255,255,255,0.1);
        color: #ffffff;
        transition: 0.2s ease;
        outline: none;
        box-shadow: 0 0 8px rgba(44,191,174,0.25);
    }

    .login-2fa-digit:focus {
        border-color: #2CBFAE;
        box-shadow: 0 0 12px rgba(44,191,174,0.8);
        background: rgba(255,255,255,0.15);
    }

    /* Verify / Resend buttons */
    .cp-btn {
        width: 100%;
        padding: 0.65rem 1rem;
        border-radius: 10px;
        font-weight: 600;
        text-align: center;
        cursor: pointer;
        border: none;
        transition: 0.2s ease;
        font-family: 'Poppins', sans-serif;
    }

    .cp-btn-teal,
    .cp-teal-btn {
        background: #2CBFAE;
        color: #0A2A4D;
    }
    .cp-btn-teal:hover {
        background: #25a99a;
        color: #ffffff;
    }

    .cp-btn-navy,
    .cp-navy-btn {
        background: #104976;
        color: white;
    }
    .cp-btn-navy:hover {
        background: #0c3a5e;
    }

    /* Debug box */
    .debug-box {
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.2);
        color: #fff;
        padding: .75rem;
        border-radius: 10px;
        margin-bottom: 1.25rem;
    }
</style>

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
                <a href="https://www.linkedin.com/company/sharplync"><img src="{{ asset('images/linkedin.png') }}" alt="LinkedIn"></a>
                <a href="https://www.facebook.com/SharpLync"><img src="{{ asset('images/facebook.png') }}" alt="Facebook"></a>
                <a href="mailto:info@sharplync.com.au"><img src="{{ asset('images/email.png') }}" alt="Email"></a>
            </div>
        </div>
    </footer>

    <!-- ========================= SCRIPTS ========================= -->
    <script>
        // Toggle overlay menu
        function toggleMenu() {
            const overlay = document.getElementById('overlayMenu');
            overlay.classList.toggle('show');
            document.body.style.overflow = overlay.classList.contains('show') ? 'hidden' : 'auto';
        }

        // Fade-in on scroll
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
