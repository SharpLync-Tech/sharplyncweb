{{-- 
  Layout: customers/layouts/customer-layout.blade.php
  Version: v1.7 (Glass Header + Power Icon Logout)
  Last updated: 13 Nov 2025 by Max (ChatGPT)
  Description:
  - Adds SharpLync-branded glass header with logo and power icon logout.
  - Fully isolated from main site layout.
  - Uses /public/css/customer.css for all styling.
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync Customer Portal')</title>
    <meta name="description" content="Access your SharpLync customer portal for account details, billing, and support.">
    <link rel="stylesheet" href="{{ asset('css/customer.css') }}">
    <link rel="icon" type="image/png" href="/images/favicon.png">
</head>

<body>
    {{-- ===== CUSTOMER HEADER ===== --}}
    <header class="customer-header">
        <div class="logo">
            <a href="{{ route('customer.portal') }}">
                <img src="/images/sharplync-logo.png" alt="SharpLync Logo">
            </a>
        </div>

        <div class="nav-right">
            <span class="nav-welcome">
                Welcome, {{ Auth::guard('customer')->user()->first_name ?? 'User' }}
            </span>

            {{-- Logout form with Sharp power icon --}}
            <form action="{{ route('customer.logout') }}" method="POST" class="logout-inline">
                @csrf
                <button type="submit" title="Log out">
                    <img src="/images/logout.png" alt="Logout">
                </button>
            </form>
        </div>
    </header>

    {{-- ===== MAIN CONTENT ===== --}}
    <main class="customer-main">
        @yield('content')
    </main>

    {{-- ===== FOOTER ===== --}}
    <footer class="customer-footer">
        <p>© {{ date('Y') }} SharpLync Pty Ltd. All rights reserved.</p>
        <p>Old School Support, <span class="highlight">Modern Results</span></p>
    </footer>

    @yield('scripts')
</body>
</html>