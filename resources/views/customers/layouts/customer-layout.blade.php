{{-- 
  Layout: customers/layouts/customer-layout.blade.php
  Version: v2.4 (Local Quill + Clean Script Ordering)
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | SharpLync Portal</title>

    {{-- CSRF for AJAX calls --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Base customer portal stylesheet --}}
    <link rel="stylesheet" href="/css/customer.css?v=3003">

    {{-- LOCAL Quill styles --}}
    <link href="{{ secure_asset('quill/quill.core.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('quill/quill.snow.css') }}" rel="stylesheet">

    {{-- LOCAL Emoji plugin styles --}}
    <link href="{{ secure_asset('quill/quill-emoji.css') }}" rel="stylesheet">

    {{-- Page-specific styles --}}
    @stack('styles')
</head>

<body class="cp-root">

<header class="cp-header">
    <div class="cp-logo">
        <img src="/images/sharplync-logo.png" alt="SharpLync Logo">
    </div>
    <div class="cp-welcome">
        Welcome, {{ Auth::user()->first_name ?? 'User' }}
        <form method="POST" action="{{ route('customer.logout') }}" class="cp-logout-inline">
            @csrf
            <button type="submit">⏻</button>
        </form>
    </div>
</header>

<main class="cp-main">
    @yield('content')
</main>

<footer class="cp-footer">
    © 2025 SharpLync Pty Ltd · All rights reserved · Straightforward Support,
    <span class="cp-hl">Modern Results</span>
</footer>

{{-- Page Scripts --}}
@yield('scripts')
@stack('scripts')

{{-- LOCAL Quill Core --}}
<script src="{{ secure_asset('quill/quill.min.js') }}"></script>

{{-- LOCAL Emoji Plugin --}}
<script src="{{ secure_asset('quill/quill-emoji.js') }}"></script>

</body>
</html>
