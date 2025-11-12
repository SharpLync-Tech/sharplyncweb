{{-- 
  Layout: customers/layouts/customer-layout.blade.php
  Version: v1.8 (Dual Logout: Header + Floating for Mobile)
  Last updated: 13 Nov 2025 by Max (ChatGPT)
  Description:
  - Keeps glass header logout for desktop.
  - Adds floating ⏻ logout button for mobile view.
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

            {{-- Desktop Logout (Header) --}}
            <form action="{{ route('customer.logout') }}" method="POST" class="logout-inline desktop-only">
                @csrf
                <button type="submit" title="Log out" class="logout-icon">⏻</button>
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

    {{-- ===== MOBILE FLOATING LOGOUT ===== --}}
    <form action="{{ route('customer.logout') }}" method="POST" class="logout-float mobile-only">
        @csrf
        <button type="submit" title="Log out" class="logout-fab">⏻</button>
    </form>

    @yield('scripts')
</body>
</html>