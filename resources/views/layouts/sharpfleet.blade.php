@extends('layouts.base')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/sharpfleet/sharpfleetmain.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
@endsection

@section('content')
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
    <footer style="background: var(--secondary-color); color: var(--text-light); padding: 20px 0; text-align: center; margin-top: 40px;">
        <div class="sharpfleet-container">
            <p>&copy; 2025 SharpFleet. Modern Fleet Management for the Digital Age.</p>
        </div>
    </footer>
@endsection
