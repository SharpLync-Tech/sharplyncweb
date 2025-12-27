<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Portal')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Bootstrap from an allowed CSP source (see SecurityHeaders middleware: unpkg is allowed) --}}
    <link href="https://unpkg.com/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="{{ asset('css/admin/admin-theme.css') }}?v=20251227" rel="stylesheet">
    <link href="{{ asset('css/admin/admin-legacy.css') }}?v=20251227" rel="stylesheet">

    @stack('styles')

</head>

<body class="admin-portal">

<nav class="navbar navbar-expand-lg navbar-dark sl-navbar sticky-top border-bottom" style="border-bottom-color: rgba(255,255,255,0.12) !important;">
    <div class="container-fluid">
        <button class="btn btn-outline-light d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Open admin navigation">
            Menu
        </button>

        <a class="navbar-brand d-flex align-items-center" href="{{ route('admin.portal') }}">
            <span>Admin Portal</span>
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

            <a href="{{ url('/admin/logout') }}" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row" style="min-height: calc(100vh - 62px);">

        {{-- Desktop sidebar --}}
        <aside class="col-lg-3 col-xl-2 d-none d-lg-block p-0">
            <div class="h-100 sl-sidebar" style="background: var(--sl-navy-800);">
                <nav class="p-3 nav nav-pills flex-column">
                    <a href="{{ route('admin.portal') }}" class="nav-link {{ request()->is('admin') || request()->is('admin/portal') ? 'active' : '' }}">
                        Portal Home
                    </a>
                    <a href="{{ url('/admin/dashboard') }}" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->is('admin/customers*') ? 'active' : '' }}">
                        Customers
                    </a>
                    <a href="{{ route('admin.testimonials.index') }}" class="nav-link {{ request()->is('admin/testimonials*') ? 'active' : '' }}">
                        Testimonials
                    </a>

                    <div class="sl-section-title">Devices</div>
                    <a href="{{ route('admin.devices.index') }}" class="nav-link {{ request()->is('admin/devices') ? 'active' : '' }}">
                        All Devices
                    </a>
                    <a href="{{ route('admin.devices.unassigned') }}" class="nav-link {{ request()->is('admin/devices/unassigned') ? 'active' : '' }}">
                        Unassigned
                    </a>
                    <a href="{{ route('admin.devices.import') }}" class="nav-link {{ request()->is('admin/devices/import') ? 'active' : '' }}">
                        Import Audit
                    </a>

                    <div class="sl-section-title">Support</div>
                    <a href="{{ route('admin.support.sms.index') }}" class="nav-link {{ request()->is('admin/support/sms') ? 'active' : '' }}">
                        Verification SMS
                    </a>
                    <a href="{{ route('admin.support.sms.general') }}" class="nav-link {{ request()->is('admin/support/sms/general') ? 'active' : '' }}">
                        Send SMS (General)
                    </a>
                    <a href="{{ route('admin.support.sms.logs') }}" class="nav-link {{ request()->is('admin/support/sms/logs') ? 'active' : '' }}">
                        SMS Logs
                    </a>

                    <div class="sl-section-title">CMS</div>
                    <a href="{{ route('admin.cms.menu.index') }}" class="nav-link {{ request()->is('admin/cms/menu*') ? 'active' : '' }}">
                        Menu Items
                    </a>
                    <a href="{{ route('admin.cms.pages.index') }}" class="nav-link {{ request()->is('admin/cms/pages*') ? 'active' : '' }}">
                        Pages
                    </a>
                    <a href="{{ route('admin.cms.services.index') }}" class="nav-link {{ request()->is('admin/cms/services*') ? 'active' : '' }}">
                        Services
                    </a>
                    <a href="{{ route('admin.cms.footer.index') }}" class="nav-link {{ request()->is('admin/cms/footer*') ? 'active' : '' }}">
                        Footer Links
                    </a>
                    <a href="{{ route('admin.cms.contact.index') }}" class="nav-link {{ request()->is('admin/cms/contact*') ? 'active' : '' }}">
                        Contact Info
                    </a>
                    <a href="{{ route('admin.cms.seo.index') }}" class="nav-link {{ request()->is('admin/cms/seo*') ? 'active' : '' }}">
                        SEO Meta
                    </a>
                    <a href="{{ route('admin.cms.about.sections.index') }}" class="nav-link {{ request()->is('admin/cms/about/sections*') ? 'active' : '' }}">
                        About Sections
                    </a>
                    <a href="{{ route('admin.cms.about.values.index') }}" class="nav-link {{ request()->is('admin/cms/about/values*') ? 'active' : '' }}">
                        About Values
                    </a>
                    <a href="{{ route('admin.cms.about.timeline.index') }}" class="nav-link {{ request()->is('admin/cms/about/timeline*') ? 'active' : '' }}">
                        About Timeline
                    </a>

                    <div class="sl-section-title">SharpFleet Admin</div>
                    <a href="{{ route('admin.sharpfleet') }}" class="nav-link" target="_blank" rel="noopener">
                        SharpFleet Admin Portal
                    </a>
                    <a href="{{ route('admin.sharpfleet', ['to' => '/app/sharpfleet/admin/vehicles']) }}" class="nav-link" target="_blank" rel="noopener">Vehicles</a>
                    <a href="{{ route('admin.sharpfleet', ['to' => '/app/sharpfleet/admin/bookings']) }}" class="nav-link" target="_blank" rel="noopener">Bookings</a>
                    <a href="{{ route('admin.sharpfleet', ['to' => '/app/sharpfleet/admin/faults']) }}" class="nav-link" target="_blank" rel="noopener">Faults</a>
                    <a href="{{ route('admin.sharpfleet', ['to' => '/app/sharpfleet/admin/reports/trips']) }}" class="nav-link" target="_blank" rel="noopener">Reports (Trips)</a>
                    <a href="{{ route('admin.sharpfleet', ['to' => '/app/sharpfleet/admin/reports/vehicles']) }}" class="nav-link" target="_blank" rel="noopener">Reports (Vehicles)</a>
                    <a href="{{ route('admin.sharpfleet', ['to' => '/app/sharpfleet/admin/users']) }}" class="nav-link" target="_blank" rel="noopener">Users</a>
                    <a href="{{ route('admin.sharpfleet', ['to' => '/app/sharpfleet/admin/settings']) }}" class="nav-link" target="_blank" rel="noopener">Settings</a>

                    <div class="sl-section-title">System</div>
                    <a href="{{ route('admin.pulse.index') }}" class="nav-link {{ request()->is('admin/pulse*') ? 'active' : '' }}">
                        Pulse Feed
                    </a>
                    <a href="{{ route('admin.components.index') }}" class="nav-link {{ request()->is('admin/components*') ? 'active' : '' }}">
                        Components
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->is('admin/settings') ? 'active' : '' }}">
                        Settings
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
            <nav class="p-3 nav nav-pills flex-column">
                <a href="{{ route('admin.portal') }}" class="nav-link {{ request()->is('admin') || request()->is('admin/portal') ? 'active' : '' }}">Portal Home</a>
                <a href="{{ url('/admin/dashboard') }}" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->is('admin/customers*') ? 'active' : '' }}">Customers</a>
                <a href="{{ route('admin.testimonials.index') }}" class="nav-link {{ request()->is('admin/testimonials*') ? 'active' : '' }}">Testimonials</a>

                <div class="sl-section-title">Devices</div>
                <a href="{{ route('admin.devices.index') }}" class="nav-link {{ request()->is('admin/devices') ? 'active' : '' }}">All Devices</a>
                <a href="{{ route('admin.devices.unassigned') }}" class="nav-link {{ request()->is('admin/devices/unassigned') ? 'active' : '' }}">Unassigned</a>
                <a href="{{ route('admin.devices.import') }}" class="nav-link {{ request()->is('admin/devices/import') ? 'active' : '' }}">Import Audit</a>

                <div class="sl-section-title">Support</div>
                <a href="{{ route('admin.support.sms.index') }}" class="nav-link {{ request()->is('admin/support/sms') ? 'active' : '' }}">Verification SMS</a>
                <a href="{{ route('admin.support.sms.general') }}" class="nav-link {{ request()->is('admin/support/sms/general') ? 'active' : '' }}">Send SMS (General)</a>
                <a href="{{ route('admin.support.sms.logs') }}" class="nav-link {{ request()->is('admin/support/sms/logs') ? 'active' : '' }}">SMS Logs</a>

                <div class="sl-section-title">CMS</div>
                <a href="{{ route('admin.cms.menu.index') }}" class="nav-link {{ request()->is('admin/cms/menu*') ? 'active' : '' }}">Menu Items</a>
                <a href="{{ route('admin.cms.pages.index') }}" class="nav-link {{ request()->is('admin/cms/pages*') ? 'active' : '' }}">Pages</a>
                <a href="{{ route('admin.cms.services.index') }}" class="nav-link {{ request()->is('admin/cms/services*') ? 'active' : '' }}">Services</a>
                <a href="{{ route('admin.cms.footer.index') }}" class="nav-link {{ request()->is('admin/cms/footer*') ? 'active' : '' }}">Footer Links</a>
                <a href="{{ route('admin.cms.contact.index') }}" class="nav-link {{ request()->is('admin/cms/contact*') ? 'active' : '' }}">Contact Info</a>
                <a href="{{ route('admin.cms.seo.index') }}" class="nav-link {{ request()->is('admin/cms/seo*') ? 'active' : '' }}">SEO Meta</a>
                <a href="{{ route('admin.cms.about.sections.index') }}" class="nav-link {{ request()->is('admin/cms/about/sections*') ? 'active' : '' }}">About Sections</a>
                <a href="{{ route('admin.cms.about.values.index') }}" class="nav-link {{ request()->is('admin/cms/about/values*') ? 'active' : '' }}">About Values</a>
                <a href="{{ route('admin.cms.about.timeline.index') }}" class="nav-link {{ request()->is('admin/cms/about/timeline*') ? 'active' : '' }}">About Timeline</a>

                <div class="sl-section-title">SharpFleet Admin</div>
                <a href="{{ route('admin.sharpfleet') }}" class="nav-link" target="_blank" rel="noopener">SharpFleet Admin Portal</a>
                <a href="{{ route('admin.sharpfleet', ['to' => '/app/sharpfleet/admin/vehicles']) }}" class="nav-link" target="_blank" rel="noopener">Vehicles</a>
                <a href="{{ route('admin.sharpfleet', ['to' => '/app/sharpfleet/admin/bookings']) }}" class="nav-link" target="_blank" rel="noopener">Bookings</a>
                <a href="{{ route('admin.sharpfleet', ['to' => '/app/sharpfleet/admin/faults']) }}" class="nav-link" target="_blank" rel="noopener">Faults</a>
                <a href="{{ route('admin.sharpfleet', ['to' => '/app/sharpfleet/admin/reports/trips']) }}" class="nav-link" target="_blank" rel="noopener">Reports (Trips)</a>
                <a href="{{ route('admin.sharpfleet', ['to' => '/app/sharpfleet/admin/reports/vehicles']) }}" class="nav-link" target="_blank" rel="noopener">Reports (Vehicles)</a>
                <a href="{{ route('admin.sharpfleet', ['to' => '/app/sharpfleet/admin/users']) }}" class="nav-link" target="_blank" rel="noopener">Users</a>
                <a href="{{ route('admin.sharpfleet', ['to' => '/app/sharpfleet/admin/settings']) }}" class="nav-link" target="_blank" rel="noopener">Settings</a>

                <div class="sl-section-title">System</div>
                <a href="{{ route('admin.pulse.index') }}" class="nav-link {{ request()->is('admin/pulse*') ? 'active' : '' }}">Pulse Feed</a>
                <a href="{{ route('admin.components.index') }}" class="nav-link {{ request()->is('admin/components*') ? 'active' : '' }}">Components</a>
                <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->is('admin/settings') ? 'active' : '' }}">Settings</a>
            </nav>
        </div>
    </div>
</div>

<script src="https://unpkg.com/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')

</body>
</html>
