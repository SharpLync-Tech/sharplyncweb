@extends('admin.layouts.admin-layout')

@section('title', 'SharpFleet Organisation')

@section('content')
<div class="container-fluid">
    <div class="sl-page-header d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h2 class="fw-semibold">{{ $organisation->name ?? 'Organisation' }}</h2>
            <div class="sl-subtitle small">SharpFleet subscriber (organisation) details.</div>
            <div class="text-muted small">All times shown in AEST (Brisbane time).</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-primary" href="{{ route('admin.sharpfleet.organisations.edit', $organisation->id) }}">Edit subscriber</a>
            <a class="btn btn-outline-secondary" href="{{ route('admin.sharpfleet.platform') }}">Back to subscribers</a>
        </div>
    </div>

    @if(session('stripe_checkout_url'))
        <div class="alert alert-info">
            <div class="fw-semibold">Stripe Checkout link created</div>
            <div class="small">
                <a href="{{ session('stripe_checkout_url') }}" target="_blank" rel="noopener">Open Stripe Checkout</a>
            </div>
        </div>
    @endif

    @if(session('stripe_uncancel_result'))
        <div class="alert alert-success">
            <div class="fw-semibold">Stripe subscription re-enabled</div>
            <div class="small">{{ (session('stripe_uncancel_result')['subscription_id'] ?? '') }}</div>
        </div>
    @endif

    @if(session('stripe_cancel_result'))
        <div class="alert alert-warning">
            <div class="fw-semibold">Stripe subscription cancellation scheduled</div>
            <div class="small">{{ (session('stripe_cancel_result')['subscription_id'] ?? '') }}</div>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-12 col-xl-5">
            <div class="card sl-card">
                <div class="card-header py-3">
                    <div class="fw-semibold">Organisation</div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="text-muted small">Organisation ID</div>
                            <div class="fw-semibold">{{ $organisation->id }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Industry</div>
                            <div class="fw-semibold">{{ $organisation->industry ?? '—' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Company type</div>
                            <div class="fw-semibold">{{ $organisation->company_type ?? '—' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Trial ends</div>
                            <div class="fw-semibold">
                                @if(!empty($organisation->trial_ends_at))
                                    {{ \Carbon\Carbon::parse($organisation->trial_ends_at, 'UTC')->timezone($displayTimezone ?? 'Australia/Brisbane')->format('d M Y, H:i') }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Subscriber timezone</div>
                            <div class="fw-semibold">{{ $timezone ?? 'Australia/Brisbane' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card sl-card">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <div class="fw-semibold">Subscription &amp; Billing</div>
                    <span class="text-muted small">From SharpFleet DB</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="fw-semibold mb-2">Current estimate</div>
                        <div class="d-flex flex-wrap gap-3">
                            <div>
                                <div class="text-muted small">Active vehicles</div>
                                <div class="fw-semibold">{{ (int) ($activeVehiclesCount ?? 0) }}</div>
                            </div>
                            <div>
                                <div class="text-muted small">Estimated monthly cost</div>
                                <div class="fw-semibold">${{ number_format((float) (($billingEstimate['monthlyPrice'] ?? 0)), 2) }}</div>
                                <div class="text-muted small">{{ $billingEstimate['breakdown'] ?? '' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="fw-semibold mb-2">Billing identifiers (from organisations.settings)</div>
                    @php
                        $b = $billingFromSettings ?? [];
                        $bs = $billingSummary ?? [];
                        $effectiveMode = (string) ($bs['effective_mode'] ?? '');
                        $overrideUntilLocal = $bs['access_override_until_local'] ?? null;
                    @endphp
                    <div class="table-responsive mb-3">
                        <table class="table table-sm align-middle mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width: 220px;">Billing mode</td>
                                    <td>
                                        @if($effectiveMode === 'complimentary')
                                            Complimentary / Free
                                        @elseif($effectiveMode === 'manual_invoice')
                                            Manual invoice
                                        @elseif($effectiveMode === 'stripe')
                                            Stripe subscription
                                        @else
                                            Trial
                                        @endif
                                    </td>
                                </tr>
                                @if($effectiveMode === 'complimentary' || $effectiveMode === 'manual_invoice')
                                    <tr>
                                        <td class="text-muted">Access until (Brisbane)</td>
                                        <td>
                                            @if(!empty($overrideUntilLocal))
                                                {{ $overrideUntilLocal->timezone($displayTimezone ?? 'Australia/Brisbane')->format('d M Y, H:i') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="text-muted" style="width: 220px;">Stripe subscription status</td>
                                    <td>{{ !empty($b['subscription_status']) ? $b['subscription_status'] : '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Subscription started</td>
                                    <td>{{ !empty($b['subscription_started_at']) ? $b['subscription_started_at'] : '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Stripe customer</td>
                                    <td>{{ !empty($b['stripe_customer_id']) ? $b['stripe_customer_id'] : '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Stripe subscription</td>
                                    <td>{{ !empty($b['stripe_subscription_id']) ? $b['stripe_subscription_id'] : '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Stripe price</td>
                                    <td>{{ !empty($b['stripe_price_id']) ? $b['stripe_price_id'] : '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="fw-semibold mb-2">Recent billing activity</div>
                    @if(empty($recentBillingLogs) || count($recentBillingLogs) === 0)
                        <div class="text-muted">No recent billing audit events found (or audit table missing).</div>
                    @else
                        <div class="table-responsive mb-3">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 180px;">When (Brisbane)</th>
                                        <th>Action</th>
                                        <th>Actor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentBillingLogs as $log)
                                        <tr>
                                            <td style="white-space:nowrap;">
                                                @if(!empty($log->created_at))
                                                    {{ \Carbon\Carbon::parse($log->created_at, 'UTC')->timezone($displayTimezone ?? 'Australia/Brisbane')->format('d M Y H:i:s') }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $log->action ?? '—' }}</div>
                                                @php
                                                    $ctxJson = (string) ($log->context_json ?? '');
                                                    $ctxArr = $ctxJson !== '' ? (json_decode($ctxJson, true) ?? []) : [];
                                                @endphp
                                                @if(!empty($ctxArr['monthly_estimate']))
                                                    @php
                                                        $me = $ctxArr['monthly_estimate'] ?? [];
                                                    @endphp
                                                    <div class="text-muted small">
                                                        ${{ number_format((float) ($me['from'] ?? 0), 2) }} → ${{ number_format((float) ($me['to'] ?? 0), 2) }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="text-muted small">{{ $log->actor_type ?? '—' }}</div>
                                                <div class="text-muted small">{{ $log->actor_email ?? '—' }}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="fw-semibold mb-2">Stripe invoices</div>
                    @if(!empty($stripeInvoicesError))
                        <div class="text-muted">Unable to load Stripe invoices: {{ $stripeInvoicesError }}</div>
                    @elseif(empty($stripeInvoices) || count($stripeInvoices) === 0)
                        <div class="text-muted">No invoices found (or Stripe not configured / missing Stripe customer ID).</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Created</th>
                                        <th>Invoice</th>
                                        <th>Status</th>
                                        <th class="text-end">Total</th>
                                        <th>Links</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stripeInvoices as $inv)
                                        @php
                                            $currency = strtoupper((string) ($inv['currency'] ?? ''));
                                            $total = isset($inv['total']) ? ((int) $inv['total'] / 100) : null;
                                            $created = !empty($inv['created']) ? \Carbon\Carbon::createFromTimestamp((int) $inv['created'], 'UTC')->timezone($displayTimezone ?? 'Australia/Brisbane')->format('d M Y') : '—';
                                        @endphp
                                        <tr>
                                            <td style="white-space:nowrap;">{{ $created }}</td>
                                            <td class="fw-semibold">{{ $inv['number'] ?: ($inv['id'] ?? '—') }}</td>
                                            <td>{{ $inv['status'] ?: '—' }}</td>
                                            <td class="text-end">@if(!is_null($total)) {{ $currency }} ${{ number_format((float) $total, 2) }} @else — @endif</td>
                                            <td>
                                                @if(!empty($inv['hosted_invoice_url']))
                                                    <a href="{{ $inv['hosted_invoice_url'] }}" target="_blank" rel="noopener">View</a>
                                                @endif
                                                @if(!empty($inv['invoice_pdf']))
                                                    @if(!empty($inv['hosted_invoice_url']))
                                                        <span class="text-muted">·</span>
                                                    @endif
                                                    <a href="{{ $inv['invoice_pdf'] }}" target="_blank" rel="noopener">PDF</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card sl-card">
                <div class="card-header py-3">
                    <div class="fw-semibold">Subscriber Data</div>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 bg-white">
                                <div class="text-muted small">Users</div>
                                <div class="h4 mb-2 fw-semibold">{{ $usersCount }}</div>
                                <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.sharpfleet.organisations.users', $organisation->id) }}">View users</a>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 bg-white">
                                <div class="text-muted small">Vehicles</div>
                                <div class="h4 mb-2 fw-semibold">{{ $vehiclesCount }}</div>
                                <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.sharpfleet.organisations.vehicles', $organisation->id) }}">View vehicles</a>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 bg-white">
                                <div class="text-muted small">Actions</div>
                                <div class="text-muted mb-2">Billing setup and subscription changes can be added here next.</div>
                                <span class="badge text-bg-light border">Read-only (MVP)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
