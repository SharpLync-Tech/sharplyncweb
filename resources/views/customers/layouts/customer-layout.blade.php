{{-- 
  Layout: customers/layouts/customer-layout.blade.php
  Version: v2.0 (Final Stable Portal Layout)
  Description:
  - Matches standalone portal visuals
  - Desktop logout (⏻) in header
  - Mobile floating logout FAB
  - All .cp-* classes scoped to avoid conflicts
--}}

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'SharpLync Portal')</title>
  <link rel="stylesheet" href="{{ asset('css/customer.css') }}">
  <link rel="icon" type="image/png" href="/images/favicon.png">
</head>

<body class="cp-root">

  {{-- ===== HEADER ===== --}}
  <header class="cp-header">
    <a class="cp-logo" href="{{ route('customer.portal') }}">
      <img src="/images/sharplync-logo.png" alt="SharpLync Logo">
    </a>

    <div style="display:flex; align-items:center; gap:.75rem;">
      <span class="cp-welcome">Welcome, {{ Auth::guard('customer')->user()->first_name ?? 'User' }}</span>
      <div class="cp-logout-inline">
        <form action="{{ route('customer.logout') }}" method="POST">
          @csrf
          <button type="submit" title="Log out">⏻</button>
        </form>
      </div>
    </div>
  </header>

  {{-- ===== MAIN CONTENT ===== --}}
  <main class="cp-main">
    @yield('content')
  </main>

  {{-- ===== FLOATING MOBILE LOGOUT ===== --}}
  <form action="{{ route('customer.logout') }}" method="POST" class="cp-logout-fab">
    @csrf
    <button type="submit" title="Log out">⏻</button>
  </form>

  {{-- ===== FOOTER ===== --}}
  <footer class="cp-footer">
    <p>© {{ date('Y') }} SharpLync Pty Ltd. All rights reserved.</p>
    <p>Old School Support, <span class="cp-hl">Modern Results</span></p>
  </footer>

  @yield('scripts')
</body>
</html>
