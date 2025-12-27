<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SharpLync Admin Portal')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="{{ asset('css/admin/admin-theme.css') }}?v=20251227" rel="stylesheet">

    @stack('styles')

</head>

<body class="admin-portal">

<nav class="navbar navbar-expand-lg navbar-dark sl-navbar sticky-top border-bottom" style="border-bottom-color: rgba(255,255,255,0.12) !important;">
    <div class="container-fluid">
        <button class="btn btn-outline-light d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Open admin navigation">
            <i class="bi bi-list"></i>
        </button>

        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/admin/dashboard') }}">
            <img src="{{ asset('images/sharplync-logo.png') }}" alt="SharpLync" style="height:38px;width:auto;" />
            <span class="d-none d-sm-inline">Admin Portal</span>
        </a>

        <div class="ms-auto d-flex align-items-center gap-3">
            <div class="d-none d-md-flex align-items-center gap-2 text-white-50">
                <img
                    src="https://ui-avatars.com/api/?name={{ urlencode(session('admin_user')['displayName'] ?? 'SharpLync Admin') }}&background=0A2A4D&color=fff&size=40"
                    alt="Profile"
                    style="width:34px;height:34px;border-radius:999px;border:1px solid rgba(255,255,255,0.25);"
                />
                <span class="text-white" style="font-weight:500;">
                    {{ session('admin_user')['displayName'] ?? 'SharpLync Admin' }}
                </span>
            </div>

            <a href="{{ url('/admin/logout') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row" style="min-height: calc(100vh - 62px);">

        {{-- Desktop sidebar --}}
        <aside class="col-lg-3 col-xl-2 d-none d-lg-block p-0">
            <div class="h-100 sl-sidebar" style="background: var(--sl-navy-800);">
                <nav class="p-3">
                    <a href="{{ url('/admin/dashboard') }}" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->is('admin/customers*') ? 'active' : '' }}">
                        <i class="bi bi-people me-2"></i> Customers
                    </a>
                    <a href="{{ route('admin.testimonials.index') }}" class="nav-link {{ request()->is('admin/testimonials*') ? 'active' : '' }}">
                        <i class="bi bi-chat-quote me-2"></i> Testimonials
                    </a>

                    <div class="sl-section-title">Devices</div>
                    <a href="{{ route('admin.devices.index') }}" class="nav-link {{ request()->is('admin/devices') ? 'active' : '' }}">
                        <i class="bi bi-pc-display me-2"></i> All Devices
                    </a>
                    <a href="{{ route('admin.devices.unassigned') }}" class="nav-link {{ request()->is('admin/devices/unassigned') ? 'active' : '' }}">
                        <i class="bi bi-link-45deg me-2"></i> Unassigned
                    </a>
                    <a href="{{ route('admin.devices.import') }}" class="nav-link {{ request()->is('admin/devices/import') ? 'active' : '' }}">
                        <i class="bi bi-cloud-arrow-up me-2"></i> Import Audit
                    </a>

                    <div class="sl-section-title">Support</div>
                    <a href="{{ route('admin.support.sms.index') }}" class="nav-link {{ request()->is('admin/support/sms') ? 'active' : '' }}">
                        <i class="bi bi-phone me-2"></i> Verification SMS
                    </a>
                    <a href="{{ route('admin.support.sms.general') }}" class="nav-link {{ request()->is('admin/support/sms/general') ? 'active' : '' }}">
                        <i class="bi bi-send me-2"></i> Send SMS (General)
                    </a>
                    <a href="{{ route('admin.support.sms.logs') }}" class="nav-link {{ request()->is('admin/support/sms/logs') ? 'active' : '' }}">
                        <i class="bi bi-journal-text me-2"></i> SMS Logs
                    </a>

                    <div class="sl-section-title">CMS</div>
                    <a href="{{ route('admin.cms.menu.index') }}" class="nav-link {{ request()->is('admin/cms/menu*') ? 'active' : '' }}">
                        <i class="bi bi-list-ul me-2"></i> Menu Items
                    </a>
                    <a href="{{ route('admin.cms.pages.index') }}" class="nav-link {{ request()->is('admin/cms/pages*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text me-2"></i> Pages
                    </a>
                    <a href="{{ route('admin.cms.services.index') }}" class="nav-link {{ request()->is('admin/cms/services*') ? 'active' : '' }}">
                        <i class="bi bi-briefcase me-2"></i> Services
                    </a>
                    <a href="{{ route('admin.cms.footer.index') }}" class="nav-link {{ request()->is('admin/cms/footer*') ? 'active' : '' }}">
                        <i class="bi bi-layout-text-window-reverse me-2"></i> Footer Links
                    </a>
                    <a href="{{ route('admin.cms.contact.index') }}" class="nav-link {{ request()->is('admin/cms/contact*') ? 'active' : '' }}">
                        <i class="bi bi-geo-alt me-2"></i> Contact Info
                    </a>
                    <a href="{{ route('admin.cms.seo.index') }}" class="nav-link {{ request()->is('admin/cms/seo*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up-arrow me-2"></i> SEO Meta
                    </a>
                    <a href="{{ route('admin.cms.about.sections.index') }}" class="nav-link {{ request()->is('admin/cms/about/sections*') ? 'active' : '' }}">
                        <i class="bi bi-info-circle me-2"></i> About Sections
                    </a>
                    <a href="{{ route('admin.cms.about.values.index') }}" class="nav-link {{ request()->is('admin/cms/about/values*') ? 'active' : '' }}">
                        <i class="bi bi-award me-2"></i> About Values
                    </a>
                    <a href="{{ route('admin.cms.about.timeline.index') }}" class="nav-link {{ request()->is('admin/cms/about/timeline*') ? 'active' : '' }}">
                        <i class="bi bi-clock-history me-2"></i> About Timeline
                    </a>

                    <div class="sl-section-title">System</div>
                    <a href="{{ route('admin.pulse.index') }}" class="nav-link {{ request()->is('admin/pulse*') ? 'active' : '' }}">
                        <i class="bi bi-broadcast me-2"></i> Pulse Feed
                    </a>
                    <a href="{{ route('admin.components.index') }}" class="nav-link {{ request()->is('admin/components*') ? 'active' : '' }}">
                        <i class="bi bi-boxes me-2"></i> Components
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->is('admin/settings') ? 'active' : '' }}">
                        <i class="bi bi-gear me-2"></i> Settings
                    </a>
                </nav>
            </div>
        </aside>

        {{-- Main content --}}
        <main class="col-12 col-lg-9 col-xl-10 py-4">
            @yield('content')
        </main>
    </div>
</div>

{{-- Mobile offcanvas sidebar --}}
<div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel" style="background: var(--sl-navy-800) !important;">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="adminSidebarLabel">Admin Navigation</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="sl-sidebar" style="width: 100%; background: transparent;">
            <nav class="p-3">
                <a href="{{ url('/admin/dashboard') }}" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->is('admin/customers*') ? 'active' : '' }}">
                    <i class="bi bi-people me-2"></i> Customers
                </a>
                <a href="{{ route('admin.testimonials.index') }}" class="nav-link {{ request()->is('admin/testimonials*') ? 'active' : '' }}">
                    <i class="bi bi-chat-quote me-2"></i> Testimonials
                </a>

                <div class="sl-section-title">Devices</div>
                <a href="{{ route('admin.devices.index') }}" class="nav-link {{ request()->is('admin/devices') ? 'active' : '' }}">
                    <i class="bi bi-pc-display me-2"></i> All Devices
                </a>
                <a href="{{ route('admin.devices.unassigned') }}" class="nav-link {{ request()->is('admin/devices/unassigned') ? 'active' : '' }}">
                    <i class="bi bi-link-45deg me-2"></i> Unassigned
                </a>
                <a href="{{ route('admin.devices.import') }}" class="nav-link {{ request()->is('admin/devices/import') ? 'active' : '' }}">
                    <i class="bi bi-cloud-arrow-up me-2"></i> Import Audit
                </a>

                <div class="sl-section-title">Support</div>
                <a href="{{ route('admin.support.sms.index') }}" class="nav-link {{ request()->is('admin/support/sms') ? 'active' : '' }}">
                    <i class="bi bi-phone me-2"></i> Verification SMS
                </a>
                <a href="{{ route('admin.support.sms.general') }}" class="nav-link {{ request()->is('admin/support/sms/general') ? 'active' : '' }}">
                    <i class="bi bi-send me-2"></i> Send SMS (General)
                </a>
                <a href="{{ route('admin.support.sms.logs') }}" class="nav-link {{ request()->is('admin/support/sms/logs') ? 'active' : '' }}">
                    <i class="bi bi-journal-text me-2"></i> SMS Logs
                </a>

                <div class="sl-section-title">CMS</div>
                <a href="{{ route('admin.cms.menu.index') }}" class="nav-link {{ request()->is('admin/cms/menu*') ? 'active' : '' }}">
                    <i class="bi bi-list-ul me-2"></i> Menu Items
                </a>
                <a href="{{ route('admin.cms.pages.index') }}" class="nav-link {{ request()->is('admin/cms/pages*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text me-2"></i> Pages
                </a>
                <a href="{{ route('admin.cms.services.index') }}" class="nav-link {{ request()->is('admin/cms/services*') ? 'active' : '' }}">
                    <i class="bi bi-briefcase me-2"></i> Services
                </a>
                <a href="{{ route('admin.cms.footer.index') }}" class="nav-link {{ request()->is('admin/cms/footer*') ? 'active' : '' }}">
                    <i class="bi bi-layout-text-window-reverse me-2"></i> Footer Links
                </a>
                <a href="{{ route('admin.cms.contact.index') }}" class="nav-link {{ request()->is('admin/cms/contact*') ? 'active' : '' }}">
                    <i class="bi bi-geo-alt me-2"></i> Contact Info
                </a>
                <a href="{{ route('admin.cms.seo.index') }}" class="nav-link {{ request()->is('admin/cms/seo*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow me-2"></i> SEO Meta
                </a>
                <a href="{{ route('admin.cms.about.sections.index') }}" class="nav-link {{ request()->is('admin/cms/about/sections*') ? 'active' : '' }}">
                    <i class="bi bi-info-circle me-2"></i> About Sections
                </a>
                <a href="{{ route('admin.cms.about.values.index') }}" class="nav-link {{ request()->is('admin/cms/about/values*') ? 'active' : '' }}">
                    <i class="bi bi-award me-2"></i> About Values
                </a>
                <a href="{{ route('admin.cms.about.timeline.index') }}" class="nav-link {{ request()->is('admin/cms/about/timeline*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history me-2"></i> About Timeline
                </a>

                <div class="sl-section-title">System</div>
                <a href="{{ route('admin.pulse.index') }}" class="nav-link {{ request()->is('admin/pulse*') ? 'active' : '' }}">
                    <i class="bi bi-broadcast me-2"></i> Pulse Feed
                </a>
                <a href="{{ route('admin.components.index') }}" class="nav-link {{ request()->is('admin/components*') ? 'active' : '' }}">
                    <i class="bi bi-boxes me-2"></i> Components
                </a>
                <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->is('admin/settings') ? 'active' : '' }}">
                    <i class="bi bi-gear me-2"></i> Settings
                </a>
            </nav>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

@stack('scripts')

</body>
</html>
