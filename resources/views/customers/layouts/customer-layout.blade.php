{{-- 
  Layout: customers/layouts/customer-layout.blade.php
  Version: v1.0
  Last updated: 12 Nov 2025 by Max (ChatGPT)
  Description:
  Dedicated layout for the SharpLync Customer Portal ecosystem.
  Completely isolated from the main site layout.
  Uses /public/css/customer.css for all styling.
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync Portal')</title>
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

        <nav>
            <a href="{{ route('customer.portal') }}">Portal</a>
            <a href="{{ route('customer.billing') }}">Billing</a>
            <a href="{{ route('customer.security') }}">Security</a>
            <a href="{{ route('customer.support') }}">Support</a>


            {{-- Logout --}}
            <form action="{{ route('customer.logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </nav>
    </header>

    {{-- ===== MAIN CONTENT AREA ===== --}}
    <main class="customer-main">
        @yield('content')
    </main>

    {{-- ===== CUSTOMER FOOTER ===== --}}
    <footer class="customer-footer">
        <p>© {{ date('Y') }} SharpLync Pty Ltd. All rights reserved.</p>
        <p>Old School Support, <span class="highlight">Modern Results</span></p>
    </footer>

    {{-- ===== JS HOOKS ===== --}}
    @yield('scripts')
</body>
</html>