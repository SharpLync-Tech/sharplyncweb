@extends('layouts.sharpfleet')

@section('title', 'Confirm Archive Vehicle')

@section('sharpfleet-content')

<div class="max-w-700 mx-auto mt-4">

    <h1 class="page-title mb-2">Confirm archive vehicle</h1>
    <p class="page-description mb-3">
        Please confirm the updated subscription numbers before the vehicle is archived.
    </p>

    @if (session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

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

            @if(!empty($vehicleName))
                <div class="mb-2">
                    <span class="text-muted">Vehicle:</span>
                    <strong>{{ $vehicleName }}</strong>
                </div>
            @endif

            <div class="mb-2">
                <div>
                    <span class="text-muted">Vehicles:</span>
                    <strong>{{ (int) ($currentVehiclesCount ?? 0) }}</strong>
                    <span class="text-muted">→</span>
                    <strong>{{ (int) ($newVehiclesCount ?? 0) }}</strong>
                </div>
                <div class="small text-muted">(This removes this vehicle from your active vehicle count.)</div>
            </div>

            <div class="mb-2">
                <div>
                    <span class="text-muted">Estimated monthly cost:</span>
                    <strong>${{ number_format((float) ($currentMonthlyPrice ?? 0), 2) }}</strong>
                    <span class="text-muted">→</span>
                    <strong>${{ number_format((float) ($newMonthlyPrice ?? 0), 2) }}</strong>
                </div>
                <div class="small text-muted">
                    {{ $newMonthlyPriceBreakdown ?? '' }}
                </div>
                <div class="small mt-1">
                    This decrease will apply from your next monthly bill.
                </div>
                @if(($requiresContactForPricing ?? false))
                    <div class="small mt-1">Over 20 vehicles: please <a href="mailto:info@sharplync.com.au">contact us</a> for pricing.</div>
                @endif
            </div>

            <form method="POST" action="{{ url('/app/sharpfleet/admin/vehicles/' . (int) ($vehicleId ?? 0) . '/archive/confirm') }}" id="confirm_form">
                @csrf

                <label class="d-flex align-items-center gap-2 mt-2" style="cursor:pointer;">
                    <input type="checkbox" name="ack_subscription_price_decrease" id="ack_subscription_price_decrease" value="1" {{ old('ack_subscription_price_decrease') ? 'checked' : '' }}>
                    <span class="small fw-bold">I acknowledge the decrease in monthly cost and confirm this vehicle will be archived.</span>
                </label>

                @error('ack_subscription_price_decrease')
                    <div class="text-error mt-2">{{ $message }}</div>
                @enderror

                <div class="btn-group mt-3">
                    <button type="submit" class="btn btn-danger" id="confirm_btn">Confirm &amp; archive vehicle</button>
                </div>
            </form>

            <form method="POST" action="{{ url('/app/sharpfleet/admin/vehicles/' . (int) ($vehicleId ?? 0) . '/archive/cancel') }}" class="mt-2">
                @csrf
                <div class="btn-group">
                    <button type="submit" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    const ack = document.getElementById('ack_subscription_price_decrease');
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
