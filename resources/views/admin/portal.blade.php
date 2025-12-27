@extends('admin.layouts.admin-layout')

@section('title', 'Admin Portal')

@section('content')
<div class="container-fluid">
    <div class="sl-page-header mb-4">
        <h2 class="fw-semibold">Admin Portal</h2>
        <div class="sl-subtitle small">Choose a product to administer.</div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card sl-card h-100">
                <div class="card-body p-4">
                    <div class="text-uppercase small text-muted" style="letter-spacing: 0.12em;">SharpLync</div>
                    <div class="h4 mt-2 mb-2 fw-semibold">SharpLync Admin</div>
                    <div class="text-muted" style="line-height: 1.6;">
                        Customers, devices, support tickets, CMS and system settings.
                    </div>
                    <div class="mt-3">
                        <a class="btn btn-primary" href="{{ url('/admin/dashboard') }}">Open SharpLync Admin</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card sl-card h-100">
                <div class="card-body p-4">
                    <div class="text-uppercase small text-muted" style="letter-spacing: 0.12em;">SharpFleet</div>
                    <div class="h4 mt-2 mb-2 fw-semibold">SharpFleet Platform Admin</div>
                    <div class="text-muted" style="line-height: 1.6;">
                        Subscribers (organisations), users, vehicles, and subscription/billing details.
                    </div>
                    <div class="mt-3">
                        <a class="btn btn-accent" href="{{ route('admin.sharpfleet.platform') }}">Open SharpFleet Platform Admin</a>
                    </div>
                    <div class="mt-2 text-muted small">Uses your Microsoft admin session.</div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="alert alert-info sl-card mb-0" role="alert">
                As new Sharp products are added, they will appear here.
            </div>
        </div>
    </div>
</div>
@endsection
