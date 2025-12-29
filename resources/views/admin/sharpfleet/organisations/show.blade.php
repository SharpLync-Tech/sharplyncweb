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
                    @if(empty($billingKeys))
                        <div class="text-muted">No billing/subscription columns were detected on the <span class="fw-semibold">organisations</span> table.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <tbody>
                                    @foreach($billingKeys as $key)
                                        <tr>
                                            @php
                                                $label = match((string) $key) {
                                                    'trial_ends_at' => 'Trial Ends',
                                                    'subscription_ends_at' => 'Subscription Ends',
                                                    'subscription_status' => 'Subscription Status',
                                                    'subscription_id' => 'Subscription ID',
                                                    'billing_email' => 'Billing Email',
                                                    'billing_status' => 'Billing Status',
                                                    'stripe_customer_id' => 'Stripe Customer ID',
                                                    'stripe_subscription_id' => 'Stripe Subscription ID',
                                                    'stripe_price_id' => 'Stripe Price ID',
                                                    'created_at' => 'Created',
                                                    'updated_at' => 'Updated',
                                                    default => ucwords(str_replace('_', ' ', (string) $key)),
                                                };
                                            @endphp
                                            <td class="text-muted" style="width: 220px;">{{ $label }}</td>
                                            <td>
                                                @php $val = $organisation->{$key} ?? null; @endphp
                                                @if(is_null($val) || $val === '')
                                                    —
                                                @else
                                                    @php
                                                        $stringVal = is_scalar($val) ? (string) $val : null;
                                                        $isDateLike = is_string($stringVal) && (str_ends_with((string) $key, '_at') || str_contains((string) $key, 'date'));
                                                    @endphp
                                                    @if($isDateLike)
                                                        @php
                                                            $formatted = null;
                                                            try {
                                                                $formatted = \Carbon\Carbon::parse($stringVal, 'UTC')->timezone($displayTimezone ?? 'Australia/Brisbane')->format('d M Y, H:i');
                                                            } catch (\Throwable $e) {
                                                                $formatted = null;
                                                            }
                                                        @endphp
                                                        {{ $formatted ?? $stringVal }}
                                                    @else
                                                        {{ is_scalar($val) ? $val : json_encode($val) }}
                                                    @endif
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
