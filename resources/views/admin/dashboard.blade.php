@extends('admin.layouts.admin-layout')

@section('title', 'Dashboard')

@section('content')
<div class="admin-dashboard-page">

    {{-- ===========================
         HERO
    ============================ --}}
    <section class="admin-dashboard-hero">
        <div class="admin-dashboard-hero-inner">
            <div class="admin-dashboard-hero-text">
                <p class="admin-kicker">SharpLync Admin</p>
                <h1>Control centre for your<br>content, customers & support.</h1>
                <p class="admin-hero-sub">
                    From here you can manage website content, customer records, devices,
                    SMS, and support tickets — with everything organised the SharpLync way:
                    clear, simple, and secure.
                </p>

                <div class="admin-hero-badges">
                    <span class="admin-hero-pill">CMS &amp; Content</span>
                    <span class="admin-hero-pill">Customers &amp; Devices</span>
                    <span class="admin-hero-pill">Support &amp; SMS</span>
                </div>
            </div>

            <div class="admin-dashboard-hero-panel">
                <div class="admin-panel-heading">
                    Quick overview
                </div>
                <ul class="admin-panel-list">
                    <li><span class="admin-dot"></span> Review new or open support tickets</li>
                    <li><span class="admin-dot"></span> Keep pages, menus, and SEO metadata up to date</li>
                    <li><span class="admin-dot"></span> Check customer profiles and assigned devices</li>
                    <li><span class="admin-dot"></span> Monitor SMS logs and key system settings</li>
                </ul>
                <div class="admin-panel-foot">
                    Start with what matters most today — tickets, content, or customers.
                </div>
            </div>
        </div>
    </section>

    {{-- ===========================
         MAIN GRID
    ============================ --}}
    <section class="admin-dashboard-main">
        <div class="admin-dashboard-grid">

            {{-- LEFT COLUMN: STATS + SHORTCUTS --}}
            <div class="admin-dashboard-column">

                {{-- STAT CARDS --}}
                <div class="admin-stat-grid">
                    <article class="admin-stat-card">
                        <div class="admin-stat-label">Customers</div>
                        <div class="admin-stat-value">Core records &amp; profiles</div>
                        <p class="admin-stat-desc">
                            View your customer list, update contact details, and jump into
                            individual profiles.
                        </p>
                        <a href="{{ route('admin.customers.index') }}"
                           class="admin-stat-link">Open Customers</a>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-label">Support Tickets</div>
                        <div class="admin-stat-value">Queue &amp; conversations</div>
                        <p class="admin-stat-desc">
                            Check active tickets, follow conversations, and respond to customers
                            from one place.
                        </p>
                        <a href="{{ route('admin.support.tickets.index') }}"
                           class="admin-stat-link">Go to Ticket Queue</a>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-label">Devices</div>
                        <div class="admin-stat-value">Audits &amp; assignment</div>
                        <p class="admin-stat-desc">
                            Review imported devices, audit history, and unassigned hardware for
                            each customer.
                        </p>
                        <a href="{{ route('admin.devices.index') }}"
                           class="admin-stat-link">View Devices</a>
                    </article>
                </div>

                {{-- QUICK ACTIONS --}}
                <div class="admin-card">
                    <h2 class="admin-card-title">Quick actions</h2>
                    <p class="admin-card-sub">
                        Common admin tasks you’ll likely use every day.
                    </p>
                    <div class="admin-quick-actions">
                        <a href="{{ route('admin.cms.pages.create') }}" class="admin-btn admin-btn-primary">
                            New CMS Page
                        </a>
                        <a href="{{ route('admin.customers.create') }}" class="admin-btn admin-btn-ghost">
                            Add Customer
                        </a>
                        <a href="{{ route('admin.support.tickets.index') }}" class="admin-btn admin-btn-ghost">
                            Open Tickets
                        </a>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: CMS + SYSTEM --}}
            <div class="admin-dashboard-column">

                {{-- CMS PANEL --}}
                <div class="admin-card">
                    <h2 class="admin-card-title">Website &amp; CMS</h2>
                    <p class="admin-card-sub">
                        Manage the content that appears on the public SharpLync website.
                    </p>

                    <div class="admin-link-grid">
                        <a href="{{ route('admin.cms.pages.index') }}" class="admin-link-tile">
                            <span class="admin-link-title">Pages</span>
                            <span class="admin-link-text">Main site pages and content blocks.</span>
                        </a>

                        <a href="{{ route('admin.cms.menu.index') }}" class="admin-link-tile">
                            <span class="admin-link-title">Navigation / Menu</span>
                            <span class="admin-link-text">Control top-level menus and links.</span>
                        </a>

                        <a href="{{ route('admin.cms.seo.index') }}" class="admin-link-tile">
                            <span class="admin-link-title">SEO Meta</span>
                            <span class="admin-link-text">Titles, descriptions, and search metadata.</span>
                        </a>

                        <a href="{{ route('admin.cms.contact.index') }}" class="admin-link-tile">
                            <span class="admin-link-title">Contact Info</span>
                            <span class="admin-link-text">Phone, email, and key contact details.</span>
                        </a>
                    </div>
                </div>

                {{-- SYSTEM / SMS / SETTINGS --}}
                <div class="admin-card">
                    <h2 class="admin-card-title">Systems &amp; messaging</h2>
                    <p class="admin-card-sub">
                        Operational tools that keep SharpLync running smoothly.
                    </p>

                    <div class="admin-link-grid">
                        <a href="{{ route('admin.sms.index') }}" class="admin-link-tile">
                            <span class="admin-link-title">SMS Templates</span>
                            <span class="admin-link-text">Manage outgoing message templates.</span>
                        </a>

                        <a href="{{ route('admin.sms.logs') }}" class="admin-link-tile">
                            <span class="admin-link-title">SMS Logs</span>
                            <span class="admin-link-text">Review SMS history and delivery status.</span>
                        </a>

                        <a href="{{ route('admin.settings.index') }}" class="admin-link-tile">
                            <span class="admin-link-title">Admin Settings</span>
                            <span class="admin-link-text">Core configuration and system options.</span>
                        </a>

                        <a href="{{ route('admin.pulse.index') }}" class="admin-link-tile">
                            <span class="admin-link-title">SharpLync Pulse</span>
                            <span class="admin-link-text">Internal notes, tracking, and tasks.</span>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </section>

</div>

{{-- ===========================
     PAGE-SPECIFIC STYLES
     (Scoped to .admin-dashboard-page)
=========================== --}}
<style>
.admin-dashboard-page {
    font-family: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    color: #0A2A4D;
}

/* HERO */
.admin-dashboard-hero {
    background: linear-gradient(135deg, #0A2A4D 0%, #104976 40%, #2CBFAE 100%);
    padding: 2.8rem 2.4rem;
    border-radius: 18px;
    margin-bottom: 2.4rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
}

.admin-dashboard-hero-inner {
    display: grid;
    grid-template-columns: minmax(0, 3fr) minmax(0, 2.5fr);
    gap: 2.4rem;
    align-items: center;
    max-width: 1200px;
}

.admin-dashboard-hero-text {
    color: #F7F9FB;
}

.admin-kicker {
    font-size: 0.85rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.78);
    margin-bottom: 0.6rem;
}

.admin-dashboard-hero-text h1 {
    font-size: 2.1rem;
    line-height: 1.25;
    margin-bottom: 0.8rem;
    text-shadow: 0 3px 10px rgba(0,0,0,0.40);
}

.admin-hero-sub {
    font-size: 0.98rem;
    line-height: 1.7;
    max-width: 540px;
    color: rgba(233,244,243,0.92);
}

.admin-hero-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1.4rem;
}

.admin-hero-pill {
    font-size: 0.78rem;
    padding: 0.4rem 0.8rem;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,0.7);
    color: #FFFFFF;
    background: rgba(6,23,41,0.35);
}

/* Hero right panel */
.admin-dashboard-hero-panel {
    background: radial-gradient(circle at top,
        rgba(44,191,174,0.28), rgba(6,23,41,0.97));
    border-radius: 16px;
    padding: 1.6rem 1.7rem 1.5rem;
    border: 1px solid rgba(255,255,255,0.18);
    color: #E9F4F3;
    box-shadow: 0 8px 24px rgba(0,0,0,0.55);
}

.admin-panel-heading {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.9rem;
}

.admin-panel-list {
    list-style: none;
    padding: 0;
    margin: 0 0 1.1rem;
}

.admin-panel-list li {
    display: flex;
    align-items: flex-start;
    gap: 0.55rem;
    font-size: 0.9rem;
    line-height: 1.6;
    margin-bottom: 0.45rem;
}

.admin-dot {
    width: 8px;
    height: 8px;
    margin-top: 0.35rem;
    border-radius: 50%;
    background: #2CBFAE;
    box-shadow: 0 0 9px rgba(44,191,174,0.9);
    flex-shrink: 0;
}

.admin-panel-foot {
    font-size: 0.86rem;
    color: rgba(233,244,243,0.86);
    border-top: 1px solid rgba(255,255,255,0.18);
    padding-top: 0.65rem;
}

/* MAIN GRID */
.admin-dashboard-main {
    margin-top: 0.6rem;
}

.admin-dashboard-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.25fr) minmax(0, 1.15fr);
    gap: 1.8rem;
}

/* Shared card styles */
.admin-card,
.admin-stat-card,
.admin-link-tile {
    background: #FFFFFF;
    border-radius: 14px;
    box-shadow: 0 7px 20px rgba(10,42,77,0.14);
    border: 1px solid rgba(10,42,77,0.06);
}

/* STAT CARDS */
.admin-stat-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 1.2rem;
    margin-bottom: 1.7rem;
}

.admin-stat-card {
    padding: 1.2rem 1.2rem 1.15rem;
    position: relative;
    overflow: hidden;
}

.admin-stat-card::before {
    content: "";
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at top left,
        rgba(44,191,174,0.17), transparent 60%);
    opacity: 0;
    transition: opacity 0.22s ease;
}

.admin-stat-label {
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #516780;
    margin-bottom: 0.35rem;
    position: relative;
    z-index: 1;
}

.admin-stat-value {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.35rem;
    color: #0A2A4D;
    position: relative;
    z-index: 1;
}

.admin-stat-desc {
    font-size: 0.86rem;
    line-height: 1.6;
    color: #4A5D72;
    margin-bottom: 0.7rem;
    position: relative;
    z-index: 1;
}

.admin-stat-link {
    font-size: 0.86rem;
    font-weight: 600;
    text-decoration: none;
    color: #104976;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    position: relative;
    z-index: 1;
}

.admin-stat-link::after {
    content: "→";
    font-size: 0.9rem;
}

.admin-stat-card:hover::before {
    opacity: 1;
}

/* GENERIC CARD */
.admin-card {
    padding: 1.4rem 1.5rem 1.3rem;
}

.admin-card-title {
    font-size: 1.1rem;
    margin-bottom: 0.35rem;
}

.admin-card-sub {
    font-size: 0.9rem;
    color: #4A5D72;
    margin-bottom: 1rem;
}

/* QUICK ACTIONS */
.admin-quick-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
}

.admin-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.65rem 1.3rem;
    border-radius: 999px;
    font-size: 0.88rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    transition: background 0.18s ease, color 0.18s ease,
                box-shadow 0.18s ease, transform 0.12s ease;
}

.admin-btn-primary {
    background-color: #2CBFAE;
    color: #06202A;
    box-shadow: 0 0 16px rgba(44,191,174,0.8);
}

.admin-btn-primary:hover {
    background-color: #35E0C2;
    box-shadow: 0 0 22px rgba(44,191,174,0.9);
    transform: translateY(-1px);
}

.admin-btn-ghost {
    background: #FFFFFF;
    border-color: rgba(10,42,77,0.18);
    color: #0A2A4D;
}

.admin-btn-ghost:hover {
    background: #F2F6FB;
    border-color: rgba(44,191,174,0.7);
}

/* LINK GRID */
.admin-link-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0,1fr));
    gap: 0.9rem;
}

.admin-link-tile {
    padding: 0.95rem 1rem 0.9rem;
    text-decoration: none;
    color: #0A2A4D;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    transition: transform 0.18s ease, box-shadow 0.18s ease,
                border-color 0.18s ease, background 0.18s ease;
}

.admin-link-title {
    font-size: 0.9rem;
    font-weight: 600;
}

.admin-link-text {
    font-size: 0.83rem;
    color: #4B6076;
}

.admin-link-tile:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 26px rgba(10,42,77,0.20);
    border-color: rgba(44,191,174,0.85);
    background: #F7FBFF;
}

/* RESPONSIVE */
@media (max-width: 1100px) {
    .admin-dashboard-hero-inner {
        grid-template-columns: minmax(0,1fr);
    }
    .admin-dashboard-grid {
        grid-template-columns: minmax(0,1fr);
    }
    .admin-stat-grid {
        grid-template-columns: repeat(2, minmax(0,1fr));
    }
}

@media (max-width: 800px) {
    .admin-dashboard-hero {
        padding: 2.1rem 1.6rem;
        border-radius: 16px;
    }
    .admin-stat-grid {
        grid-template-columns: minmax(0,1fr);
    }
    .admin-link-grid {
        grid-template-columns: minmax(0,1fr);
    }
}

@media (max-width: 600px) {
    .admin-dashboard-hero-text h1 {
        font-size: 1.7rem;
    }
}
</style>
@endsection
