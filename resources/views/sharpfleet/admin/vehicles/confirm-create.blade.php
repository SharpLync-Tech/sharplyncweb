@extends('layouts.sharpfleet')

@section('title', 'Confirm Add Asset')

@section('sharpfleet-content')

<div class="max-w-700 mx-auto mt-4">

    <h1 class="page-title mb-2">Confirm add asset</h1>
    <p class="page-description mb-3">
        Please confirm the updated subscription numbers before the asset is created.
    </p>

    @if ($errors->any())
        <div class="alert alert-error">
            <div>
                <strong>Please fix the errors below.</strong>
                <ul class="mb-0" style="margin-top: 8px; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="fw-bold mb-2">Subscription cost confirmation</div>

            @if(!empty($pendingVehicleName))
                <div class="mb-2">
                    <span class="text-muted">Asset:</span>
                    <strong>{{ $pendingVehicleName }}</strong>
                </div>
            @endif

            <div class="mb-2">
                <div><span class="text-muted">Vehicles:</span> <strong>{{ (int) ($newVehiclesCount ?? 0) }}</strong></div>
                <div class="small text-muted">(This includes your existing vehicles plus this new one.)</div>
            </div>

            <div class="mb-2">
                <div>
                    <span class="text-muted">New estimated monthly cost:</span>
                    <strong>${{ number_format((float) ($newMonthlyPrice ?? 0), 2) }}</strong>
                </div>
                <div class="small text-muted">
                    {{ $newMonthlyPriceBreakdown ?? '' }}
                </div>
                <div class="small mt-1">
                    This increase will be added to your next monthly bill regardless of the time of the month you add the vehicle.
                </div>
                @if(($requiresContactForPricing ?? false))
                    <div class="small mt-1">Over 20 vehicles: please <a href="mailto:info@sharplync.com.au">contact us</a> for pricing.</div>
                @endif
            </div>

            <form method="POST" action="{{ url('/app/sharpfleet/admin/vehicles/create/confirm') }}" id="confirm_form">
                @csrf

                <label class="d-flex align-items-center gap-2 mt-2" style="cursor:pointer;">
                    <input type="checkbox" name="ack_subscription_price_increase" id="ack_subscription_price_increase" value="1" {{ old('ack_subscription_price_increase') ? 'checked' : '' }}>
                    <span class="small fw-bold">I acknowledge the increase in monthly cost.</span>
                </label>

                @error('ack_subscription_price_increase')
                    <div class="text-error mt-2">{{ $message }}</div>
                @enderror

                <div class="btn-group mt-3">
                    <button type="submit" class="btn btn-primary" id="confirm_btn">Confirm &amp; add asset</button>
                </div>
            </form>

            <form method="POST" action="{{ url('/app/sharpfleet/admin/vehicles/create/cancel') }}" class="mt-2">
                @csrf
                <div class="btn-group">
                    <button type="submit" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    const ack = document.getElementById('ack_subscription_price_increase');
    const btn = document.getElementById('confirm_btn');

    if (ack && btn) {
        function updateEnabled() {
            btn.disabled = !ack.checked;
        }

        ack.addEventListener('change', updateEnabled);
        updateEnabled();
    }
</script>

@endsection
