@extends('layouts.sharpfleet')

@section('title', 'Account & Subscription')

@section('sharpfleet-content')
<div class="hero">
    <h1>Account &amp;<br><span class="highlight">Subscription</span></h1>
</div>

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning">
        {{ session('warning') }}
    </div>
@endif

@if(isset($organisation) && isset($organisation->account_type) && (string) $organisation->account_type === 'personal')
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="fw-bold">Upgrade to Sole Trader</div>
                    <div class="text-muted small">This unlocks Customers and other Sole Trader features. No setup wizard rerun.</div>
                </div>

                <form method="POST" action="/app/sharpfleet/admin/account/upgrade-to-sole-trader">
                    @csrf
                    <button class="btn btn-primary" type="submit">Upgrade</button>
                </form>
            </div>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-body">
        @php
            $billingSummary = $billingSummary ?? [];
            $effectiveMode = (string) ($billingSummary['effective_mode'] ?? 'trial');
            $overrideUntilLocal = $billingSummary['access_override_until_local'] ?? null;

            // Ensure pricing breakdown never renders broken UTF-8 characters
            $safePriceBreakdown = str_replace(['Ã—', '×'], '×', $monthlyPriceBreakdown ?? '');
        @endphp

        @if($effectiveMode === 'stripe')
            <div class="d-flex justify-between align-items-center flex-wrap gap-2 mb-2">
                <div>
                    <div class="fw-bold">Subscription active</div>
                    <div class="text-muted small">Billing is calculated per active vehicle.</div>
                </div>

                <form method="POST" action="/app/sharpfleet/admin/account/cancel-subscription">
                    @csrf
                    <button class="btn btn-danger" type="submit">Cancel Subscription</button>
                </form>
            </div>

            <div class="grid grid-2 mt-3">
                <div class="stats-card" style="margin:0;">
                    <div class="stats-number">{{ (int) $vehiclesCount }}</div>
                    <div class="stats-label">Active vehicles</div>
                </div>
                <div class="stats-card" style="margin:0;">
                    <div class="stats-number">AU${{ number_format((float) $monthlyPrice, 2) }}</div>
                    <div class="stats-label">Estimated monthly cost</div>
                </div>
            </div>

            <div class="text-muted small mt-2">
                Pricing: AU$3.50 per vehicle/month for vehicles 1-10, then AU$2.50 per vehicle/month for vehicles 11+
                AU$ ({{ $safePriceBreakdown }}).
                @if($requiresContactForPricing)
                    <div class="mt-1">
                        Over 20 vehicles: please <a href="mailto:info@sharplync.com.au">contact us</a> for pricing.
                    </div>
                @endif

                <div class="mt-2">
                    Cancelling your subscription switches your account to read-only access (reports only). You will have access to your data for one year, after that the account will be archived.
                </div>
            </div>

        @elseif($effectiveMode === 'complimentary' || $effectiveMode === 'manual_invoice')
            <div class="d-flex justify-between align-items-center flex-wrap gap-2 mb-2">
                <div>
                    <div class="fw-bold">Access active</div>
                    <div class="text-muted small">
                        @if($effectiveMode === 'complimentary')
                            Your account is currently complimentary (free).
                        @elseif($effectiveMode === 'manual_invoice')
                            Your account is currently on manual invoicing.
                        @else
                            Your account has an access override.
                        @endif
                    </div>
                </div>
            </div>

            @if(!empty($overrideUntilLocal))
                <div class="text-muted small mb-0">Access until: {{ $overrideUntilLocal->format('d M Y, H:i') }}</div>
            @endif

            <div class="text-muted small mt-3">
                Subscription changes are managed by your platform admin while this override is active.
            </div>

        @else
            {{-- FREE TRIAL --}}
            <div class="d-flex justify-between align-items-center flex-wrap gap-2 mb-2">
                <div>
                    <div class="fw-bold">Free trial</div>
                    <div class="text-muted small">
                        @if(is_null($trialDaysRemaining))
                            Trial status unavailable.
                        @elseif($trialDaysRemaining > 0)
                            {{ (int) $trialDaysRemaining }} day(s) remaining.
                        @else
                            Trial ended.
                        @endif
                    </div>
                </div>

                <button
                    type="button"
                    id="sf-show-subscribe-step"
                    class="btn btn-sm sf-btn-spotlight"
                >
                    Subscribe
                </button>
            </div>

            @if($trialEndsAt)
                <div class="text-muted small mb-3">
                    Trial ends: {{ $trialEndsAt->format('d M Y, H:i') }}
                </div>
            @endif

            <div
                id="sf-subscribe-step"
                class="stats-card text-left mt-3"
                style="margin:0; display:none;"
            >
                <div class="fw-bold mb-1">Confirm subscription</div>
                <div class="text-muted small mb-3">
                    Review your estimated monthly cost before subscribing.
                </div>

                <div class="grid grid-2" style="gap:16px;">
                    <div class="stats-card" style="margin:0;">
                        <div class="stats-number">{{ (int) $vehiclesCount }}</div>
                        <div class="stats-label">Active vehicles</div>
                    </div>
                    <div class="stats-card" style="margin:0;">
                        <div class="stats-number">AU${{ number_format((float) $monthlyPrice, 2) }}</div>
                        <div class="stats-label">Estimated monthly cost</div>
                    </div>
                </div>

                <div class="text-muted small mt-2">
                    AU$3.50 per vehicle/month for vehicles 1-10, then AU$2.50 per vehicle/month for vehicles 11-20
                    AU$ ({{ $safePriceBreakdown }}).
                    @if($requiresContactForPricing)
                        <div class="mt-1">
                            Over 20 vehicles: please <a href="mailto:info@sharplync.com.au">contact us</a> for pricing.
                        </div>
                    @endif
                </div>

                <div class="mt-3">
                    <label class="d-flex align-items-center gap-2" style="cursor:pointer;">
                        <input type="checkbox" id="sf-accept-terms">
                        <span class="small">
                            I agree to the
                            <a href="/policies/sharpfleet-terms" target="_blank" rel="noopener">
                                Terms &amp; Conditions
                            </a>
                        </span>
                    </label>
                </div>

                <form method="POST" action="/app/sharpfleet/admin/account/subscribe" class="mt-3">
                    @csrf
                    <button
                        type="submit"
                        id="sf-confirm-subscribe"
                        class="btn sf-btn-spotlight"
                        disabled
                    >
                        Confirm &amp; Subscribe
                    </button>
                </form>
            </div>

            @if($hasCancelRequest)
                <div class="alert alert-error mb-0">
                    Trial cancellation requested. Your account is now read-only (reports only).
                </div>
            @else
                <div class="alert alert-info" style="align-items:flex-start;">
                    <div>
                        <div class="fw-bold mb-1">Cancel trial</div>
                        <div class="small">
                            Cancelling your trial switches your account to read-only access (reports only). You will have access to your data for one year, after that the account will be archived.
                        </div>
                        <form method="POST" action="/app/sharpfleet/admin/account/cancel-trial" class="mt-2">
                            @csrf
                            <button class="btn btn-danger" type="submit">Cancel Trial</button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="text-muted small mt-3">
                Pricing when subscribed: AU$3.50 per vehicle per month for vehicles 1-10, then AU$2.50 per vehicle per month for vehicles 11-20. Over 20 vehicles: contact us for pricing.
            </div>
        @endif
    </div>
</div>

<div class="card mt-3">
    <div class="card-body">
        <div class="fw-bold mb-2">Recent billing activity</div>

        @if(!empty($billingActivityTableMissing) && $billingActivityTableMissing)
            <div class="alert alert-warning mb-0">
                Billing activity is not available (audit log table missing).
            </div>
        @elseif(($billingActivity ?? collect())->isEmpty())
            <div class="text-muted small">No billing activity yet.</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>User</th>
                        <th>IP</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($billingActivity as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->created_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ $row->action }}</td>
                            <td>{{ $row->user_name ?? 'System' }}</td>
                            <td>{{ $row->ip_address ?? '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<script>
(function () {
    const showBtn   = document.getElementById('sf-show-subscribe-step');
    const step      = document.getElementById('sf-subscribe-step');
    const checkbox  = document.getElementById('sf-accept-terms');
    const submitBtn = document.getElementById('sf-confirm-subscribe');

    if (!showBtn || !step || !checkbox || !submitBtn) return;

    showBtn.addEventListener('click', function () {
        step.style.display = 'block';
        step.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    checkbox.addEventListener('change', function () {
        submitBtn.disabled = !checkbox.checked;
    });
})();
</script>
@endsection
