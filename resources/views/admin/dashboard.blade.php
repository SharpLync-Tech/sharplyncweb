@extends('admin.layouts.admin-layout')

@section('title', 'Dashboard')

@section('content')

<div class="container-fluid">
    <div class="sl-page-header d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h2 class="fw-semibold">Dashboard</h2>
            <div class="sl-subtitle small">Your central hub for customers, content &amp; support.</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-primary btn-sm" href="{{ route('admin.customers.index') }}">
                Customers
            </a>
            <a class="btn btn-accent btn-sm" href="{{ route('admin.support.tickets.index') }}">
                Tickets
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card sl-card overflow-hidden" style="background: linear-gradient(135deg, var(--sl-navy), var(--sl-navy-600) 45%, rgba(44,191,174,0.95)); color: #fff;">
                <div class="card-body p-4 p-lg-5">
                    <div class="text-uppercase small" style="letter-spacing: 0.12em; opacity: 0.85;">SharpLync Admin</div>
                    <h1 class="h3 mt-2 mb-2" style="font-weight: 700;">Premium control centre</h1>
                    <p class="mb-0" style="max-width: 820px; opacity: 0.92; line-height: 1.65;">
                        Manage customers, devices, support tickets, and CMS content from one secure portal.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card sl-card">
                <div class="card-header py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="fw-semibold">Quick actions</div>
                        <span class="text-muted small">Common admin tasks</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <a class="btn btn-outline-primary w-100 text-start" href="{{ route('admin.customers.index') }}">
                                Browse customers
                            </a>
                        </div>
                        <div class="col-12 col-md-6">
                            <a class="btn btn-outline-primary w-100 text-start" href="{{ route('admin.devices.index') }}">
                                View all devices
                            </a>
                        </div>
                        <div class="col-12 col-md-6">
                            <a class="btn btn-outline-primary w-100 text-start" href="{{ route('admin.support.sms.index') }}">
                                Send verification SMS
                            </a>
                        </div>
                        <div class="col-12 col-md-6">
                            <a class="btn btn-outline-primary w-100 text-start" href="{{ route('admin.pulse.index') }}">
                                Manage pulse feed
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card sl-card">
                <div class="card-header py-3">
                    <div class="fw-semibold">What you can manage</div>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0" style="display:grid; gap: 10px;">
                        <li><span>Customer records and device audit history</span></li>
                        <li><span>Support tickets and internal notes</span></li>
                        <li><span>CMS pages, menus, services, SEO, and blog</span></li>
                        <li><span>SMS verification, general SMS, and logs</span></li>
                        <li><span>Pulse feed and component library</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
