<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync | IT Support & Cloud Services')</title>

    <!-- Meta SEO -->
    <meta name="description" content="SharpLync delivers reliable IT support, cloud services, and technology solutions across the Granite Belt and beyond. Old school support, modern results.">
    <meta name="keywords" content="SharpLync, IT Support, Cloud Services, Managed IT, Granite Belt, Warwick, Stanthorpe, Tenterfield">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://sharplync.com.au/">
    <meta name="author" content="SharpLync Pty Ltd">
    <link rel="sitemap" type="application/xml" title="Sitemap" href="/sitemap.xml">

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

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Main site styles -->
    <link rel="stylesheet" href="{{ secure_asset('css/sharplync.css') }}">
    @stack('styles')

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('/favicon.ico') }}">

    <!-- ============================================= -->
    <!-- LOGIN-TIME 2FA MODAL CSS (Globally available) -->
    <!-- ============================================= -->
    <style>
        /* Modal backdrop */
        .cp-modal-backdrop {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.55);
            backdrop-filter: blur(2px);
            z-index: 9999;
        }

        .cp-modal-backdrop.cp-modal-visible {
            display: flex !important;
        }

        /* Modal card/sheet */
        .cp-modal-sheet {
            background: #fff;
            border-radius: 14px;
            padding: 1.6rem 1.4rem;
            width: 94%;
            max-width: 460px;
            box-shadow: 0 18px 50px rgba(0,0,0,0.25);
            animation: modalPop .25s ease-out;
        }

        @keyframes modalPop {
            0%   { transform: translateY(20px); opacity: 0; }
            100% { transform: translateY(0);    opacity: 1; }
        }

        /* Make sure modal sits ABOVE hero, header, menus */
        .modal-open {
            overflow: hidden !important;
        }
    </style>
</head>

<body class="cp-root">
    @yield('content')

    @stack('scripts')
</body>
</html>
