<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $seoSiteUrl = rtrim((string) config('seo.site_url'), '/');
        $seoPath = request()->path();
        $seoDefaultCanonical = $seoSiteUrl . (($seoPath === '/' || $seoPath === '') ? '/' : '/' . ltrim($seoPath, '/'));
        $seoTitle = trim($__env->yieldContent('title')) ?: config('seo.default_title');
        $seoDescription = trim($__env->yieldContent('meta_description')) ?: config('seo.default_description');
        $seoCanonical = trim($__env->yieldContent('canonical')) ?: $seoDefaultCanonical;
        $seoPrivatePath = request()->is([
            'admin', 'admin/*', 'auth/*', 'customers', 'customers/*', 'app', 'app/*',
            'login', 'register', 'set-password/*', 'password/*', 'forgot-password',
            'password-reset*', 'verify/*', 'marketing/confirm/*', 'marketing/unsubscribe/*',
            'marketing/preferences/*', 'marketing/admin', 'marketing/admin/*',
            'support-admin', 'support-admin/*',
        ]);
        $seoRobots = trim($__env->yieldContent('robots')) ?: ($seoPrivatePath ? 'noindex, nofollow' : 'index, follow');
        $seoImage = trim($__env->yieldContent('og_image')) ?: $seoSiteUrl . config('seo.default_image');
        $business = config('seo.business');
        $businessSchema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => ['LocalBusiness', 'ProfessionalService'],
                    '@id' => $business['id'],
                    'name' => $business['name'],
                    'url' => $seoSiteUrl . '/',
                    'logo' => ['@type' => 'ImageObject', 'url' => $business['logo']],
                    'image' => $seoImage,
                    'telephone' => $business['telephone'],
                    'email' => $business['email'],
                    'description' => config('seo.default_description'),
                    'address' => [
                        '@type' => 'PostalAddress',
                        'addressLocality' => $business['locality'],
                        'addressRegion' => $business['region'],
                        'postalCode' => $business['postal_code'],
                        'addressCountry' => $business['country'],
                    ],
                    'areaServed' => array_map(fn ($area) => ['@type' => 'Place', 'name' => $area], $business['areas_served']),
                    'contactPoint' => [
                        '@type' => 'ContactPoint',
                        'telephone' => $business['telephone'],
                        'email' => $business['email'],
                        'contactType' => 'customer support',
                        'areaServed' => 'AU',
                        'availableLanguage' => 'English',
                    ],
                    'sameAs' => $business['same_as'],
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $seoSiteUrl . '/#website',
                    'url' => $seoSiteUrl . '/',
                    'name' => config('seo.site_name'),
                    'publisher' => ['@id' => $business['id']],
                    'inLanguage' => 'en-AU',
                ],
            ],
        ];
    @endphp
    <!-- Google tag (GA4) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-2SCQ2YCEW8"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-2SCQ2YCEW8');
    </script>

    <!-- Google Ads tag (AW-17789891633) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17789891633"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'AW-17789891633');
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seoTitle }}</title>

        <!-- Primary SEO -->
        <meta name="description" content="{{ $seoDescription }}">
        <meta name="robots" content="{{ $seoRobots }}">
        <meta name="facebook-domain-verification" content="nlot90unrp0fw2s4uquw1q8hnxnh7a" />

        <!-- Open Graph / social -->
        <meta property="og:title" content="{{ $seoTitle }}">
        <meta property="og:description" content="{{ $seoDescription }}">
        <meta property="og:image" content="{{ $seoImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ $seoCanonical }}">
        <meta property="og:site_name" content="SharpLync">
        <meta property="og:locale" content="en_AU">
        <meta name="twitter:card" content="summary_large_image">

    <!-- Canonical -->
    <link rel="canonical" href="{{ $seoCanonical }}">

    <!-- Sitemap reference -->
    <link rel="sitemap" type="application/xml" title="Sitemap" href="/sitemap.xml">

    <meta name="author" content="SharpLync Pty Ltd">

    <script type="application/ld+json">@json($businessSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    @stack('structured_data')

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="{{ secure_asset('css/sharplync.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('css/sharplync-nav.css') }}">

    @stack('styles')

        <!-- Favicons -->
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
        <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon-48.png') }}">

        <!-- Apple Touch -->
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

        <!-- Android / Chrome -->
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('android-chrome-192.png') }}">
        <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('android-chrome-512.png') }}">

        <!-- ICO fallback -->
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
</head>


<body>

    <!-- ========================= HEADER ========================= -->
    <header class="main-header">

        <!-- LEFT -->
        <div class="nav-left">
            <a href="/" class="logo">
                <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync Logo">
            </a>
            <a href="/" class="nav-link {{ request()->is('/') ? 'nav-active' : '' }}">Home</a>
            <a href="/marketing/sharppulse" class="nav-link {{ request()->is('marketing/sharppulse') ? 'nav-active' : '' }}">SharpPulse</a>
            <a href="/services" class="nav-link {{ request()->is('services') ? 'nav-active' : '' }}">Services</a>
            <details class="nav-products">
                <summary class="nav-link {{ request()->is('products/*') ? 'nav-active' : '' }}">Products</summary>
                <div class="nav-products-menu">
                    <a href="{{ route('products.sharpfleet') }}">SharpFleet</a>
                </div>
            </details>
        </div>

        <!-- RIGHT -->
        <div class="nav-right">
            <a href="/about" class="nav-link {{ request()->is('about') ? 'nav-active' : '' }}">About</a>
            <a href="/testimonials" class="nav-link {{ request()->is('testimonials') ? 'nav-active' : '' }}">Testimonials</a>
            <a href="/contact" class="nav-link {{ request()->is('contact') ? 'nav-active' : '' }}">Contact</a>
            <a href="/policies/hub" class="nav-link {{ request()->is('policies') ? 'nav-active' : '' }}">Policies</a>
            <a href="/register" class="nav-link {{ request()->is('register') ? 'nav-active' : '' }}">Register</a>
            <a href="/login" class="nav-link {{ request()->is('login') ? 'nav-active' : '' }}">Login</a>

            <button class="hamburger" onclick="toggleMobileNav()">☰</button>
        </div>

    </header>

    <!-- ========================= MOBILE NAV ========================= -->
    <div id="mobileNav" class="mobile-nav">
        <button class="close-mobile-nav" onclick="toggleMobileNav()">×</button>

        <ul>
            <li><a href="/">Home</a></li>
            <li><a href="/marketing/sharppulse">SharpPulse</a></li>
            <li><a href="/services">Services</a></li>
            <li class="mobile-products">
                <span>Products</span>
                <a href="{{ route('products.sharpfleet') }}">SharpFleet</a>
            </li>
            <li><a href="/about">About</a></li>
            <li><a href="/testimonials">Testimonials</a></li>
            <li><a href="/contact">Contact</a></li>
            <li><a href="/policies/hub">Policies</a></li>
            <li><a href="/register">Register</a></li>
            <li><a href="/login">Login</a></li>
        </ul>
    </div>

    <!-- ========================= ORIGINAL OVERLAY MENU ========================= -->
    <div id="overlayMenu" class="overlay-menu" role="navigation">
        <button class="close-menu" onclick="toggleMenu()">×</button>

        <ul>
            @foreach(($menuItems ?? []) as $item)
                <li>
                    <a href="{{ $item->url }}" onclick="toggleMenu()" @if($item->open_in_new_tab) target="_blank" @endif>
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
            <p class="footer-local-links">
                <a href="{{ route('it-support.stanthorpe') }}">IT Support Stanthorpe</a>
                <span aria-hidden="true"> · </span>
                <a href="{{ route('computer-repairs.stanthorpe') }}">Computer Repairs Stanthorpe</a>
            </p>
            <p><span class="sl-builtby">Designed & built by SharpLync</span></p>

            <div class="social-icons">
                <a href="https://www.linkedin.com/company/sharplync"><img src="{{ asset('images/linkedin.png') }}" alt="LinkedIn"></a>
                <a href="https://www.facebook.com/SharpLync"><img src="{{ asset('images/facebook.png') }}" alt="Facebook"></a>
                <a href="mailto:info@sharplync.com.au"><img src="{{ asset('images/email.png') }}" alt="Email"></a>
            </div>
        </div>
    </footer>

    <!-- ========================= SCRIPTS ========================= -->
    <script>
        function toggleMobileNav() {
            document.getElementById('mobileNav').classList.toggle('show');
        }

        function toggleMenu() {
            const overlay = document.getElementById('overlayMenu');
            overlay.classList.toggle('show');
            document.body.style.overflow = overlay.classList.contains('show') ? 'hidden' : 'auto';
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) entry.target.classList.add('visible');
            });
        }, { threshold: 0.15 });

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.fade-section').forEach(section => observer.observe(section));
        });

        document.addEventListener('click', (event) => {
            document.querySelectorAll('.nav-products[open]').forEach(menu => {
                if (!menu.contains(event.target)) menu.removeAttribute('open');
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                document.querySelectorAll('.nav-products[open]').forEach(menu => menu.removeAttribute('open'));
            }
        });
    </script>

    @stack('scripts')

</body>
</html>
