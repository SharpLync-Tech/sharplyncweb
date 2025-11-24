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
    position: relative;
    background: #0A2A4D;
    border: 2px solid #2CBFAE;
    border-radius: 18px;
    padding: 2rem 2rem 2rem;
    width: 94%;
    max-width: 500px;
    box-shadow:
        0 0 15px rgba(44,191,174,0.25),
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

/* Close button – FIXED */
.cp-modal-close {
    position: absolute;
    top: 14px;
    right: 14px;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: 1px solid rgba(44,191,174,0.65);
    background: rgba(255,255,255,0.1);
    color: #2CBFAE;
    font-size: 22px;
    font-weight: bold;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s ease;
}

.cp-modal-close:hover {
    background: rgba(44,191,174,0.22);
    border-color: #2CBFAE;
    color: white;
    box-shadow: 0 0 8px rgba(44,191,174,0.45);
}

/* OTP DIGITS */
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

/* Buttons */
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

.cp-teal-btn {
    background: #2CBFAE;
    color: #0A2A4D;
}
.cp-teal-btn:hover {
    background: #25a99a;
    color: #ffffff;
}

.cp-navy-btn {
    background: #104976;
    color: white;
}
.cp-navy-btn:hover {
    background: #0c3a5e;
}

/* Shake error */
.shake {
    animation: shakeAnim .35s ease;
}
@keyframes shakeAnim {
    0% { transform: translateX(0); }
    20% { transform: translateX(-6px); }
    40% { transform: translateX(6px); }
    60% { transform: translateX(-6px); }
    80% { transform: translateX(6px); }
    100% { transform: translateX(0); }
}

/* Loading dots animation */
@keyframes dots {
    0% { content: ''; }
    33% { content: '.'; }
    66% { content: '..'; }
    100% { content: '...'; }
}
.loading-dots::after {
    content: '';
    animation: dots 1s infinite;
}
</style>

    @stack('scripts')   
</body>
</html>
