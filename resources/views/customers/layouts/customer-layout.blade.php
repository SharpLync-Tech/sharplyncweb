{{-- 
  Layout: customers/layouts/customer-layout.blade.php
  Version: v1.8.3 (Logout Visibility Fix + Dual Logout Confirmed)
  Description:
  - Desktop logout visible in glass header
  - Floating logout button visible on mobile
  - Safe isolation for customer portal only
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

<body class="customer-portal-body">
  {{-- ===== CUSTOMER HEADER ===== --}}
  <header class="customer-header">
    <div class="logo">
      <a href="{{ route('customer.portal') }}">
        <img src="/images/sharplync-logo.png" alt="SharpLync Logo" class="logo-img">
      </a>
    </div>

    <div class="nav-right">
      <span class="nav-welcome">
        Welcome, {{ Auth::guard('customer')->user()->first_name ?? 'User' }}
      </span>

      {{-- Desktop Logout Icon --}}
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

  {{-- ===== FLOATING LOGOUT (MOBILE) ===== --}}
  <form action="{{ route('customer.logout') }}" method="POST" class="logout-float mobile-only">
    @csrf
    <button type="submit" title="Log out" class="logout-fab">⏻</button>
  </form>

  @yield('scripts')
</body>
</html>
