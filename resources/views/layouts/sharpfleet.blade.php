<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SharpFleet - Advanced Fleet Management')</title>

    <link rel="manifest" href="/app/sharpfleet.webmanifest">

    <meta name="theme-color" content="#ffffff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SharpFleet">

    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/android-chrome-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/android-chrome-512.png">

    <!-- Fonts (match SharpLync) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- SharpFleet CSS (use secure_asset to prevent mixed-content blocking on HTTPS) -->
    <link rel="stylesheet" href="{{ secure_asset('css/sharpfleet/sharpfleetmain.css') }}?v={{ @filemtime(public_path('css/sharpfleet/sharpfleetmain.css')) ?: time() }}">
    
    @stack('styles')
</head>
<body>
    {{-- SharpFleet Header / Navigation --}}
    <header class="sharpfleet-header">
        <div class="sharpfleet-container">
            <nav class="sharpfleet-nav">
                <a href="/sharpfleet" class="sharpfleet-logo">
                    @php($sharpfleetLogoPath = public_path('images/sharpfleet/logo.png'))
                    @if (is_string($sharpfleetLogoPath) && file_exists($sharpfleetLogoPath))
                        <img src="{{ asset('images/sharpfleet/logo.png') }}?v={{ @filemtime($sharpfleetLogoPath) ?: time() }}" alt="SharpFleet Logo">
                    @endif                    
                </a>

                <div class="sharpfleet-nav-links">
                    @if(session()->has('sharpfleet.user'))
                        @php($sfRole = \App\Support\SharpFleet\Roles::normalize(session('sharpfleet.user.role')))
                        @if(\App\Support\SharpFleet\Roles::isAdminPortal(session('sharpfleet.user')))
                            <div class="sharpfleet-nav-primary">
                                <div class="sharpfleet-nav-dropdown">
                                    <button type="button" class="sharpfleet-nav-link sharpfleet-nav-dropdown-toggle {{ request()->is('app/sharpfleet/admin') || request()->is('app/sharpfleet/admin/vehicles*') || request()->is('app/sharpfleet/admin/bookings*') ? 'is-active' : '' }}">Fleet</button>
                                    <div class="sharpfleet-nav-dropdown-menu">
                                        <a href="/app/sharpfleet/admin" class="sharpfleet-nav-dropdown-item">Dashboard</a>
                                        @if($sfRole !== \App\Support\SharpFleet\Roles::BOOKING_ADMIN)
                                            <a href="/app/sharpfleet/admin/vehicles" class="sharpfleet-nav-dropdown-item">Vehicles</a>
                                        @endif
                                        <a href="/app/sharpfleet/admin/bookings" class="sharpfleet-nav-dropdown-item">Bookings</a>
                                    </div>
                                </div>

                                @if($sfRole !== \App\Support\SharpFleet\Roles::BOOKING_ADMIN)
                                    <div class="sharpfleet-nav-dropdown">
                                        <button type="button" class="sharpfleet-nav-link sharpfleet-nav-dropdown-toggle {{ request()->is('app/sharpfleet/admin/reminders*') || request()->is('app/sharpfleet/admin/safety-checks*') ? 'is-active' : '' }}">Operations</button>
                                        <div class="sharpfleet-nav-dropdown-menu">
                                            <a href="/app/sharpfleet/admin/reminders" class="sharpfleet-nav-dropdown-item">Reminders</a>
                                            <a href="/app/sharpfleet/admin/safety-checks" class="sharpfleet-nav-dropdown-item">Safety Checks</a>
                                        </div>
                                    </div>

                                    <div class="sharpfleet-nav-dropdown">
                                        <button type="button" class="sharpfleet-nav-link sharpfleet-nav-dropdown-toggle {{ request()->is('app/sharpfleet/admin/customers*') ? 'is-active' : '' }}">Customers</button>
                                        <div class="sharpfleet-nav-dropdown-menu">
                                            <a href="/app/sharpfleet/admin/customers" class="sharpfleet-nav-dropdown-item">View Customers</a>
                                            <a href="/app/sharpfleet/admin/customers/create" class="sharpfleet-nav-dropdown-item">Add Customers</a>
                                        </div>
                                    </div>

                                    <div class="sharpfleet-nav-dropdown">
                                        <button type="button" class="sharpfleet-nav-link sharpfleet-nav-dropdown-toggle {{ request()->is('app/sharpfleet/admin/reports*') || request()->is('app/sharpfleet/admin/faults*') ? 'is-active' : '' }}">Reports</button>
                                        <div class="sharpfleet-nav-dropdown-menu">
                                            <a href="/app/sharpfleet/admin/reports/trips" class="sharpfleet-nav-dropdown-item">Trip Reports</a>
                                            <a href="/app/sharpfleet/admin/faults" class="sharpfleet-nav-dropdown-item">Faults</a>
                                        </div>
                                    </div>
                                @endif

                                @if($sfRole === \App\Support\SharpFleet\Roles::COMPANY_ADMIN || $sfRole === \App\Support\SharpFleet\Roles::BRANCH_ADMIN)
                                    <div class="sharpfleet-nav-dropdown">
                                        <button type="button" class="sharpfleet-nav-link sharpfleet-nav-dropdown-toggle {{ request()->is('app/sharpfleet/admin/company*') || request()->is('app/sharpfleet/admin/branches*') || request()->is('app/sharpfleet/admin/users*') || request()->is('app/sharpfleet/admin/settings*') ? 'is-active' : '' }}">Company</button>
                                        <div class="sharpfleet-nav-dropdown-menu">
                                            <a href="/app/sharpfleet/admin/users" class="sharpfleet-nav-dropdown-item">Users / Drivers</a>

                                            @if($sfRole === \App\Support\SharpFleet\Roles::COMPANY_ADMIN)
                                                <a href="/app/sharpfleet/admin/company" class="sharpfleet-nav-dropdown-item">Company Overview</a>
                                                <a href="/app/sharpfleet/admin/company/profile" class="sharpfleet-nav-dropdown-item">Edit Company Details</a>
                                                <a href="/app/sharpfleet/admin/branches" class="sharpfleet-nav-dropdown-item">Branches</a>
                                                <a href="/app/sharpfleet/admin/settings" class="sharpfleet-nav-dropdown-item">Company Settings</a>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <div class="sharpfleet-nav-dropdown">
                                    <button type="button" class="sharpfleet-nav-link sharpfleet-nav-dropdown-toggle {{ request()->is('app/sharpfleet/admin/help*') || request()->is('app/sharpfleet/admin/about*') ? 'is-active' : '' }}">Help</button>
                                    <div class="sharpfleet-nav-dropdown-menu">
                                        <a href="/app/sharpfleet/admin/help" class="sharpfleet-nav-dropdown-item">Instructions</a>
                                        <a href="/app/sharpfleet/admin/about" class="sharpfleet-nav-dropdown-item">About</a>
                                    </div>
                                </div>
                                <a href="/app/sharpfleet/driver" class="sharpfleet-nav-link {{ request()->is('app/sharpfleet/driver*') ? 'is-active' : '' }}">Driver</a>
                            </div>
                        @else
                            <a href="/app/sharpfleet/driver" class="sharpfleet-nav-link {{ request()->is('app/sharpfleet/driver*') ? 'is-active' : '' }}">Dashboard</a>
                            <a href="/app/sharpfleet/bookings" class="sharpfleet-nav-link {{ request()->is('app/sharpfleet/bookings*') ? 'is-active' : '' }}">Bookings</a>
                            <div class="sharpfleet-nav-dropdown">
                                <button type="button" class="sharpfleet-nav-link sharpfleet-nav-dropdown-toggle {{ request()->is('app/sharpfleet/driver/help*') || request()->is('app/sharpfleet/driver/about*') ? 'is-active' : '' }}">Help</button>
                                <div class="sharpfleet-nav-dropdown-menu">
                                    <a href="/app/sharpfleet/driver/help" class="sharpfleet-nav-dropdown-item">Instructions</a>
                                    <a href="/app/sharpfleet/driver/about" class="sharpfleet-nav-dropdown-item">About</a>
                                </div>
                            </div>
                        @endif
                        <div class="sharpfleet-user-info">
                            <div class="sharpfleet-user-avatar">
                                {{ strtoupper(substr(session('sharpfleet.user.first_name'), 0, 1)) }}
                            </div>
                            <span>{{ session('sharpfleet.user.first_name') }}</span>
                            <a href="/app/sharpfleet/logout" class="sharpfleet-nav-link">Logout</a>
                        </div>
                    @else
                        <a href="/sharpfleet/why" class="sharpfleet-nav-link">Why SharpLync</a>
                        <a href="/sharpfleet/about" class="sharpfleet-nav-link">About</a>
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

    <div id="sfConfirmModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5);">
        <div class="card" style="max-width:520px; margin:10vh auto;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <h3 class="mb-1" id="sfConfirmTitle">Confirm</h3>
                        <p class="text-muted mb-0" id="sfConfirmMessage">Are you sure?</p>
                    </div>
                    <button type="button"
                            class="sf-modal-close"
                            id="sfConfirmClose"
                            aria-label="Close"
                            title="Close"
                            style="">
                        &times;
                    </button>
                </div>

                <div class="mt-3"></div>

                <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-secondary" id="sfConfirmCancel">Cancel</button>
                    <button type="button" class="btn btn-primary" id="sfConfirmOk">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <div id="sfNoticeModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5);">
        <div class="card" style="max-width:520px; margin:10vh auto;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <h3 class="mb-1" id="sfNoticeTitle">Notice</h3>
                        <p class="text-muted mb-0" id="sfNoticeMessage"></p>
                    </div>
                    <button type="button"
                            class="sf-modal-close"
                            id="sfNoticeClose"
                            aria-label="Close"
                            title="Close"
                            style="">
                        &times;
                    </button>
                </div>

                <div class="mt-3"></div>

                <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-primary" id="sfNoticeOk">OK</button>
                </div>
            </div>
        </div>
    </div>

    {{-- SharpFleet Footer --}}
<footer class="sharpfleet-footer">
    <div class="sharpfleet-container">
        <p>
            &copy; {{ date('Y') }} SharpFleet is a product of
            <a href="https://sharplync.com.au"
               target="_blank"
               rel="noopener">                
                <span class="highlight">SharpLync Pty Ltd</span>                
            </a>
        </p>
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

    <script>
        // SharpFleet-only PWA support (scope limited to /app/sharpfleet/).
        (function() {
            if (!('serviceWorker' in navigator)) return;

            let refreshing = false;
            navigator.serviceWorker.addEventListener('controllerchange', function () {
                if (refreshing) return;
                refreshing = true;

                // Only hard reload when online (offline should keep the cached shell).
                if (navigator.onLine) {
                    window.location.reload();
                }
            });

            function isRootScoped(reg) {
                try {
                    const scope = (reg && reg.scope) ? reg.scope : '';
                    const scriptURL = (reg && reg.active && reg.active.scriptURL) ? reg.active.scriptURL : '';
                    return scope === (location.origin + '/') && scriptURL.endsWith('/sw.js');
                } catch (e) {
                    return false;
                }
            }

            window.addEventListener('load', async function() {
                try {
                    // If an earlier root-scoped SW was installed, remove it so SharpLync isn't affected.
                    const regs = await navigator.serviceWorker.getRegistrations();
                    for (const reg of regs) {
                        if (isRootScoped(reg)) {
                            await reg.unregister();
                        }
                    }
                } catch (e) {
                    // ignore
                }

                try {
                    const reg = await navigator.serviceWorker.register('/app/sharpfleet-sw.js', { scope: '/app/sharpfleet/' });

                    // Proactively check for updates when the app is opened.
                    try { await reg.update(); } catch (e) { /* ignore */ }

                    // If a new SW is installed, reload once it's controlling the page.
                    reg.addEventListener('updatefound', function () {
                        const newWorker = reg.installing;
                        if (!newWorker) return;

                        newWorker.addEventListener('statechange', function () {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller && navigator.onLine) {
                                window.location.reload();
                            }
                        });
                    });

                    // Re-check for updates when the app tab becomes visible.
                    document.addEventListener('visibilitychange', function () {
                        if (document.visibilityState === 'visible' && navigator.onLine) {
                            try { reg.update(); } catch (e) { /* ignore */ }
                        }
                    });
                } catch (e) {
                    // fail silently
                }
            });
        })();
    </script>

    <script>
        // Replace native browser popups (confirm/alert) with SharpFleet modals.
        (function () {
            const confirmModal = document.getElementById('sfConfirmModal');
            const confirmTitle = document.getElementById('sfConfirmTitle');
            const confirmMessage = document.getElementById('sfConfirmMessage');
            const confirmOk = document.getElementById('sfConfirmOk');
            const confirmCancel = document.getElementById('sfConfirmCancel');
            const confirmClose = document.getElementById('sfConfirmClose');

            const noticeModal = document.getElementById('sfNoticeModal');
            const noticeTitle = document.getElementById('sfNoticeTitle');
            const noticeMessage = document.getElementById('sfNoticeMessage');
            const noticeOk = document.getElementById('sfNoticeOk');
            const noticeClose = document.getElementById('sfNoticeClose');

            let pendingConfirm = null;

            function hide(el) {
                if (el) el.style.display = 'none';
            }

            function show(el) {
                if (el) el.style.display = 'block';
            }

            function closeConfirm() {
                pendingConfirm = null;
                if (confirmOk) {
                    confirmOk.disabled = false;
                    confirmOk.className = 'btn btn-primary';
                    confirmOk.textContent = 'Confirm';
                }
                hide(confirmModal);
            }

            function openConfirm(opts) {
                if (!confirmModal || !confirmTitle || !confirmMessage || !confirmOk) return;

                confirmTitle.textContent = (opts && opts.title) ? opts.title : 'Confirm';
                confirmMessage.textContent = (opts && opts.message) ? opts.message : 'Are you sure?';
                confirmOk.textContent = (opts && opts.confirmText) ? opts.confirmText : 'Confirm';
                confirmOk.className = (opts && opts.confirmClass) ? opts.confirmClass : 'btn btn-primary';

                pendingConfirm = (opts && typeof opts.onConfirm === 'function') ? opts.onConfirm : null;
                show(confirmModal);
            }

            function closeNotice() {
                hide(noticeModal);
            }

            function openNotice(opts) {
                if (!noticeModal || !noticeTitle || !noticeMessage) return;
                noticeTitle.textContent = (opts && opts.title) ? opts.title : 'Notice';
                noticeMessage.textContent = (opts && opts.message) ? opts.message : '';
                show(noticeModal);
            }

            if (confirmOk) {
                confirmOk.addEventListener('click', function () {
                    const fn = pendingConfirm;
                    if (!fn) {
                        closeConfirm();
                        return;
                    }
                    confirmOk.disabled = true;
                    try {
                        fn();
                    } finally {
                        // If navigation happens, this won't matter; otherwise keep UI sane.
                        closeConfirm();
                    }
                });
            }
            if (confirmCancel) confirmCancel.addEventListener('click', closeConfirm);
            if (confirmClose) confirmClose.addEventListener('click', closeConfirm);
            if (confirmModal) {
                confirmModal.addEventListener('click', function (e) {
                    if (e.target === confirmModal) closeConfirm();
                });
            }

            if (noticeOk) noticeOk.addEventListener('click', closeNotice);
            if (noticeClose) noticeClose.addEventListener('click', closeNotice);
            if (noticeModal) {
                noticeModal.addEventListener('click', function (e) {
                    if (e.target === noticeModal) closeNotice();
                });
            }

            // Expose minimal API for inline scripts.
            window.SharpFleetModal = {
                confirm: openConfirm,
                notice: openNotice,
            };

            // Auto-wire forms that request confirmation.
            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!(form instanceof HTMLFormElement)) return;
                if (!form.hasAttribute('data-sf-confirm')) return;

                e.preventDefault();

                const title = form.getAttribute('data-sf-confirm-title') || 'Confirm';
                const message = form.getAttribute('data-sf-confirm-message') || 'Are you sure?';
                const confirmText = form.getAttribute('data-sf-confirm-text') || 'Confirm';
                const confirmVariant = form.getAttribute('data-sf-confirm-variant') || 'primary';
                const confirmClass = confirmVariant === 'danger' ? 'btn btn-danger' : 'btn btn-primary';

                openConfirm({
                    title,
                    message,
                    confirmText,
                    confirmClass,
                    onConfirm: function () {
                        form.submit();
                    }
                });
            }, true);
        })();
    </script>
    @stack('scripts')
</body>
</html>
