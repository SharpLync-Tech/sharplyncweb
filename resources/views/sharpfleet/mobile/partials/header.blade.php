<header class="sf-mobile-header">
    <div class="sf-mobile-header-bg"></div>
    <div class="sf-mobile-header-overlay"></div>

    <div class="sf-mobile-header-inner">
        <div class="sf-mobile-header-left">
            <img
                src="{{ asset('images/sharpfleet/logo.png') }}"
                alt="SharpFleet"
                class="sf-mobile-header-logo"
            >
        </div>

    </div>

    @hasSection('mobile-header-meta')
        <div class="sf-mobile-header-meta">
            @yield('mobile-header-meta')
        </div>
    @endif
</header>
