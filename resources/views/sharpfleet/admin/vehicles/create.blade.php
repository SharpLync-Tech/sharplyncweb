@extends('layouts.sharpfleet')

@section('title', 'Add Vehicle')

@section('sharpfleet-content')

<div style="max-width:800px;margin:40px auto;padding:0 16px;">

    <h1 style="margin-bottom:8px;">Add Vehicle</h1>
    <p style="margin-bottom:24px;color:#6b7280;">
        Add a vehicle or asset to your organisation. Usage tracking determines what drivers must record
        when operating this vehicle.
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

            {{-- Vehicle name --}}
            <label style="display:block;font-weight:600;margin-bottom:6px;">Vehicle name</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   style="width:100%;padding:10px;margin-bottom:12px;">
            @error('name')
                <div style="color:#b91c1c;margin-bottom:12px;">{{ $message }}</div>
            @enderror

            {{-- Registration --}}
            <label style="display:block;font-weight:600;margin-bottom:6px;">Registration number</label>
            <input type="text" name="registration_number" value="{{ old('registration_number') }}" required
                   style="width:100%;padding:10px;margin-bottom:12px;">
            @error('registration_number')
                <div style="color:#b91c1c;margin-bottom:12px;">{{ $message }}</div>
            @enderror

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
                    <select name="vehicle_type" required
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
                        Example: Light Vehicle, Heavy Vehicle, Machinery, Asset
                    </div>
                </div>
            </div>

            {{-- Tracking mode --}}
            <label style="display:block;font-weight:600;margin:16px 0 6px;">
                Usage tracking
            </label>
            @php $tm = old('tracking_mode', 'distance'); @endphp
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
                This controls what drivers are required to record when using this vehicle.
            </div>

            {{-- Wheelchair --}}
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
                Save Vehicle
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

@endsection
