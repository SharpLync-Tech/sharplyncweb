{{-- 
  Layout: customers/layouts/customer-layout.blade.php
  Version: v2.0 (Customer Portal Layout - Stable)
  Description:
  - Provides header, footer, and cp-root background wrapper
--}}

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title') | SharpLync Portal</title>
  <link rel="stylesheet" href="/css/customer.css">
</head>
<body class="cp-root">
  <header class="cp-header">
    <div class="cp-logo"><img src="/images/sharplync-logo.png" alt="SharpLync Logo"></div>
    <div class="cp-welcome">
      Welcome, {{ Auth::user()->first_name ?? 'User' }}
      <form method="POST" action="{{ route('customer.logout') }}" class="cp-logout-inline">@csrf
        <button type="submit">⏻</button>
      </form>
    </div>
  </header>

  <main class="cp-main">
    @yield('content')
  </main>

  <footer class="cp-footer">
    © 2025 SharpLync Pty Ltd · All rights reserved · Old School Support, <span class="cp-hl">Modern Results</span>
  </footer>

  @yield('scripts')
</body>
</html>
