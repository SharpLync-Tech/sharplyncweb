<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SharpFleet - Advanced Fleet Management')</title>

    <!-- SharpFleet CSS -->
    <link rel="stylesheet" href="{{ asset('css/sharpfleet/sharpfleetmain.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    @stack('styles')
</head>
<body>
    {{-- SharpFleet Header / Navigation --}}
    <header class="sharpfleet-header">
        <div class="sharpfleet-container">
            <nav class="sharpfleet-nav">
                <a href="/app/sharpfleet" class="sharpfleet-logo">
                    <img src="{{ asset('images/sharpfleet/logo.png') }}" alt="SharpFleet Logo" onerror="this.style.display='none'">
                    <span>SharpFleet</span>
                </a>

                <div class="sharpfleet-nav-links">
                    @if(session()->has('sharpfleet.user'))
                        @if(session('sharpfleet.user.role') === 'admin')
                            <a href="/app/sharpfleet/admin" class="sharpfleet-nav-link">Dashboard</a>
                            <a href="/app/sharpfleet/admin/vehicles" class="sharpfleet-nav-link">Vehicles</a>
                            <a href="/app/sharpfleet/admin/reports/trips" class="sharpfleet-nav-link">Reports</a>
                            <a href="/app/sharpfleet/admin/settings" class="sharpfleet-nav-link">Settings</a>
                            <a href="/app/sharpfleet/driver" class="sharpfleet-nav-link">Driver View</a>
                        @else
                            <a href="/app/sharpfleet/driver" class="sharpfleet-nav-link">Dashboard</a>
                        @endif
                        <div class="sharpfleet-user-info">
                            <div class="sharpfleet-user-avatar">
                                {{ strtoupper(substr(session('sharpfleet.user.first_name'), 0, 1)) }}
                            </div>
                            <span>{{ session('sharpfleet.user.first_name') }}</span>
                            <a href="/app/sharpfleet/logout" class="sharpfleet-nav-link">Logout</a>
                        </div>
                    @else
                        <a href="/app/sharpfleet/login" class="sharpfleet-nav-link">Login</a>
                    @endif
                </div>

                <!-- Mobile Menu Button -->
                <button class="sharpfleet-mobile-menu-btn" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </nav>
        </div>
    </header>

    {{-- SharpFleet Main Content --}}
    <main class="sharpfleet-main">
        <div class="sharpfleet-container">
            @yield('sharpfleet-content')
        </div>
    </main>

    {{-- SharpFleet Footer --}}
    <footer class="sharpfleet-footer">
        <div class="sharpfleet-container">
            <p>&copy; 2025 SharpFleet. Modern Fleet Management for the Digital Age.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Mobile menu toggle
        $(document).ready(function() {
            $('.sharpfleet-mobile-menu-btn').click(function() {
                $('.sharpfleet-nav-links').toggleClass('active');
                $(this).toggleClass('active');
            });

            // Close mobile menu when clicking outside
            $(document).click(function(e) {
                if (!$(e.target).closest('.sharpfleet-nav').length) {
                    $('.sharpfleet-nav-links').removeClass('active');
                    $('.sharpfleet-mobile-menu-btn').removeClass('active');
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
