@extends('layouts.base')

{{-- Test Layout for SharpFleet Test Homepage --}}
@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpFleet Test')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .navbar {
            background: linear-gradient(135deg, #0A2A4D 0%, #2CBFAE 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
        }

        .navbar-brand {
            font-size: 24px;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .navbar-brand:hover {
            color: #e0e0e0;
        }

        .navbar-nav {
            display: flex;
            list-style: none;
            align-items: center;
        }

        .nav-item {
            margin-left: 30px;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: #e0e0e0;
        }

        .nav-link.active {
            background: rgba(255,255,255,0.2);
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
            color: white;
            font-size: 14px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #2CBFAE;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: 600;
        }

        .dropdown {
            position: relative;
        }

        .dropdown-toggle {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            font-size: 14px;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            min-width: 200px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: block;
            padding: 12px 16px;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
        }

        .dropdown-divider {
            height: 1px;
            background: #e9ecef;
            margin: 8px 0;
        }

        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .navbar-nav {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #0A2A4D;
                flex-direction: column;
                padding: 20px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            }

            .navbar-nav.active {
                display: flex;
            }

            .nav-item {
                margin: 10px 0;
                width: 100%;
            }

            .nav-link {
                display: block;
                padding: 12px;
                text-align: center;
            }

            .mobile-menu-toggle {
                display: block;
            }
        }

        main {
            min-height: calc(100vh - 70px);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/app/sharpfleet/test-home" class="navbar-brand">
                🚛 SharpFleet
            </a>

            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                ☰
            </button>

            <ul class="navbar-nav" id="navbarNav">
                <li class="nav-item">
                    <a href="/app/sharpfleet/test-home" class="nav-link {{ request()->is('app/sharpfleet/test-home') ? 'active' : '' }}">
                        Home
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/app/sharpfleet/login" class="nav-link">
                        Login
                    </a>
                </li>

                @if(session()->has('sharpfleet.user'))
                    @if(session('sharpfleet.user.role') === 'admin')
                        <li class="nav-item">
                            <a href="/app/sharpfleet/admin" class="nav-link">
                                Admin Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/app/sharpfleet/debug" class="nav-link">
                                Debug
                            </a>
                        </li>
                    @elseif(session('sharpfleet.user.role') === 'driver')
                        <li class="nav-item">
                            <a href="/app/sharpfleet/driver" class="nav-link">
                                Driver Dashboard
                            </a>
                        </li>
                    @endif

                    <li class="nav-item dropdown">
                        <button class="dropdown-toggle">
                            <div class="user-avatar">
                                {{ strtoupper(substr(session('sharpfleet.user.name'), 0, 1)) }}
                            </div>
                            {{ session('sharpfleet.user.name') }}
                            ▼
                        </button>
                        <div class="dropdown-menu">
                            <a href="#" class="dropdown-item">Profile Settings</a>
                            <div class="dropdown-divider"></div>
                            <a href="/app/sharpfleet/logout" class="dropdown-item">Logout</a>
                        </div>
                    </li>
                @endif
            </ul>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <script>
        function toggleMobileMenu() {
            const nav = document.getElementById('navbarNav');
            nav.classList.toggle('active');
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const nav = document.getElementById('navbarNav');
            const toggle = document.querySelector('.mobile-menu-toggle');

            if (!nav.contains(event.target) && !toggle.contains(event.target)) {
                nav.classList.remove('active');
            }
        });
    </script>
</body>
</html>
@endsection