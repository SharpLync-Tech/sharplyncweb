@extends('layouts.sharpfleet')

@section('title', 'Add Vehicle / Asset')

@section('sharpfleet-content')

<div style="max-width:800px;margin:40px auto;padding:0 16px;">

    <h1 style="margin-bottom:8px;">Add Vehicle / Asset</h1>
    <p style="margin-bottom:24px;color:#6b7280;">
        Assets are identified by name. If an asset is road registered, its registration number
        will automatically be shown to drivers alongside the asset name.
    </p>

    @if ($errors->any())
        <div style="background:#fee2e2;color:#7f1d1d;padding:12px 16px;border-radius:8px;margin-bottom:24px;">
            <strong>Please fix the errors below.</strong>
        </div>
    @endif

    <form method="POST" action="{{ url('/app/sharpfleet/admin/vehicles') }}">
        @csrf

        <div style="background:white;padding:20px;border-radius:10px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.05);
                    margin-bottom:24px;">

            {{-- Asset name --}}
            <label style="display:block;font-weight:600;margin-bottom:6px;">
                Asset name / identifier
            </label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   placeholder="e.g. White Camry, Tractor 3, Forklift A"
                   style="width:100%;padding:10px;margin-bottom:6px;">
            <div style="font-size:12px;color:#6b7280;margin-bottom:12px;">
                This is how drivers and reports will identify this asset.
            </div>
            @error('name')
                <div style="color:#b91c1c;margin-bottom:12px;">{{ $message }}</div>
            @enderror

            {{-- Road registered --}}
            <input type="hidden" name="is_road_registered" value="0">

            <label style="display:block;margin:12px 0;font-weight:600;">
                <input type="checkbox"
                       id="is_road_registered"
                       name="is_road_registered"
                       value="1"
                       {{ old('is_road_registered') == 1 ? 'checked' : '' }}>
                This asset is road registered
            </label>

            <div style="font-size:12px;color:#6b7280;margin-bottom:12px;">
                Road-registered assets require a registration number and will display it to drivers.
            </div>

            {{-- Registration number --}}
            <div id="rego-wrapper">
                <label style="display:block;font-weight:600;margin-bottom:6px;">
                    Registration number
                </label>
                <input type="text" name="registration_number"
                       value="{{ old('registration_number') }}"
                       placeholder="e.g. ABC-123"
                       style="width:100%;padding:10px;margin-bottom:12px;">
                @error('registration_number')
                    <div style="color:#b91c1c;margin-bottom:12px;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Usage tracking --}}
            @php $tm = old('tracking_mode', 'distance'); @endphp
            <label style="display:block;font-weight:600;margin:16px 0 6px;">
                Usage tracking
            </label>
            <select name="tracking_mode"
                    style="width:100%;padding:10px;margin-bottom:6px;">
                <option value="distance" {{ $tm === 'distance' ? 'selected' : '' }}>
                    Distance (kilometres)
                </option>
                <option value="hours" {{ $tm === 'hours' ? 'selected' : '' }}>
                    Hours (machine hour meter)
                </option>
                <option value="none" {{ $tm === 'none' ? 'selected' : '' }}>
                    No usage tracking
                </option>
            </select>
            <div style="font-size:12px;color:#6b7280;margin-bottom:12px;">
                This controls what drivers are required to record when using this asset.
            </div>

            {{-- Make / Model --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Make</label>
                    <input type="text" name="make" value="{{ old('make') }}"
                           style="width:100%;padding:10px;margin-bottom:12px;">
                </div>

                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Model</label>
                    <input type="text" name="model" value="{{ old('model') }}"
                           style="width:100%;padding:10px;margin-bottom:12px;">
                </div>
            </div>

            {{-- Vehicle type / classification --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Vehicle type</label>
                    <select name="vehicle_type"
                            style="width:100%;padding:10px;margin-bottom:12px;">
                        @php $vt = old('vehicle_type', 'sedan'); @endphp
                        <option value="sedan" {{ $vt === 'sedan' ? 'selected' : '' }}>Sedan</option>
                        <option value="hatch" {{ $vt === 'hatch' ? 'selected' : '' }}>Hatch</option>
                        <option value="suv" {{ $vt === 'suv' ? 'selected' : '' }}>SUV</option>
                        <option value="van" {{ $vt === 'van' ? 'selected' : '' }}>Van</option>
                        <option value="bus" {{ $vt === 'bus' ? 'selected' : '' }}>Bus</option>
                        <option value="other" {{ $vt === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">
                        Vehicle classification (optional)
                    </label>
                    <input type="text" name="vehicle_class" value="{{ old('vehicle_class') }}"
                           style="width:100%;padding:10px;margin-bottom:6px;">
                    <div style="font-size:12px;color:#6b7280;">
                        Examples: Light Vehicle, Heavy Vehicle, Machinery, Asset
                    </div>
                </div>
            </div>

            {{-- Accessibility --}}
            <label style="display:block;margin:12px 0;">
                <input type="checkbox" name="wheelchair_accessible" value="1"
                    {{ old('wheelchair_accessible') ? 'checked' : '' }}>
                <strong>Wheelchair accessible</strong>
            </label>

            {{-- Notes --}}
            <label style="display:block;font-weight:600;margin-bottom:6px;">Notes (optional)</label>
            <textarea name="notes" rows="3"
                      style="width:100%;padding:10px;">{{ old('notes') }}</textarea>

        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit"
                    style="background:#2CBFAE;color:white;
                           border:none;padding:12px 20px;
                           border-radius:6px;font-weight:600;cursor:pointer;">
                Save Asset
            </button>

            <a href="{{ url('/app/sharpfleet/admin/vehicles') }}"
               style="background:#e5e7eb;color:#111827;
                      padding:12px 20px;border-radius:6px;
                      text-decoration:none;font-weight:600;">
                Cancel
            </a>
        </div>

    </form>

</div>

<script>
    const roadCheckbox = document.getElementById('is_road_registered');
    const regoWrapper  = document.getElementById('rego-wrapper');

    function toggleRego() {
        regoWrapper.style.display = roadCheckbox.checked ? 'block' : 'none';
    }

    toggleRego();
    roadCheckbox.addEventListener('change', toggleRego);
</script>

@endsection
