@extends('layouts.sharpfleet')

@section('title', 'Edit Vehicle')

@section('sharpfleet-content')

<div style="max-width:800px;margin:40px auto;padding:0 16px;">

    <h1 style="margin-bottom:8px;">Edit Vehicle</h1>
    <p style="margin-bottom:24px;color:#6b7280;">
        Registration number is locked for safety.
    </p>

    @if ($errors->any())
        <div style="background:#fee2e2;color:#7f1d1d;padding:12px 16px;border-radius:8px;margin-bottom:24px;">
            <strong>Please fix the errors below.</strong>
        </div>
    @endif

    <form method="POST" action="{{ url('/app/sharpfleet/admin/vehicles/'.$vehicle->id) }}">
        @csrf

        <div style="background:white;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.05);margin-bottom:24px;">

            <label style="display:block;font-weight:600;margin-bottom:6px;">Vehicle name</label>
            <input type="text" name="name" value="{{ old('name', $vehicle->name) }}" required
                   style="width:100%;padding:10px;margin-bottom:12px;">
            @error('name') <div style="color:#b91c1c;margin-bottom:12px;">{{ $message }}</div> @enderror

            <label style="display:block;font-weight:600;margin-bottom:6px;">Registration number (locked)</label>
            <input type="text" value="{{ $vehicle->registration_number }}" disabled
                   style="width:100%;padding:10px;margin-bottom:12px;background:#f3f4f6;">
            <div style="font-size:12px;color:#6b7280;margin-top:-6px;margin-bottom:12px;">
                If the rego is wrong, archive this vehicle and add it again with the correct rego.
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Make</label>
                    <input type="text" name="make" value="{{ old('make', $vehicle->make) }}"
                           style="width:100%;padding:10px;margin-bottom:12px;">
                    @error('make') <div style="color:#b91c1c;margin-bottom:12px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Model</label>
                    <input type="text" name="model" value="{{ old('model', $vehicle->model) }}"
                           style="width:100%;padding:10px;margin-bottom:12px;">
                    @error('model') <div style="color:#b91c1c;margin-bottom:12px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Vehicle type</label>
                    @php $vt = old('vehicle_type', $vehicle->vehicle_type); @endphp
                    <select name="vehicle_type" required style="width:100%;padding:10px;margin-bottom:12px;">
                        <option value="sedan" {{ $vt === 'sedan' ? 'selected' : '' }}>Sedan</option>
                        <option value="hatch" {{ $vt === 'hatch' ? 'selected' : '' }}>Hatch</option>
                        <option value="suv" {{ $vt === 'suv' ? 'selected' : '' }}>SUV</option>
                        <option value="van" {{ $vt === 'van' ? 'selected' : '' }}>Van</option>
                        <option value="bus" {{ $vt === 'bus' ? 'selected' : '' }}>Bus</option>
                        <option value="other" {{ $vt === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('vehicle_type') <div style="color:#b91c1c;margin-bottom:12px;">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Vehicle classification (optional)</label>
                    <input type="text" name="vehicle_class" value="{{ old('vehicle_class', $vehicle->vehicle_class) }}"
                           style="width:100%;padding:10px;margin-bottom:12px;">
                    @error('vehicle_class') <div style="color:#b91c1c;margin-bottom:12px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <label style="display:block;margin:12px 0;">
                <input type="checkbox" name="wheelchair_accessible" value="1"
                    {{ old('wheelchair_accessible', (int)$vehicle->wheelchair_accessible) ? 'checked' : '' }}>
                <strong>Wheelchair accessible</strong>
            </label>

            <label style="display:block;font-weight:600;margin-bottom:6px;">Notes (optional)</label>
            <textarea name="notes" rows="3" style="width:100%;padding:10px;">{{ old('notes', $vehicle->notes) }}</textarea>
            @error('notes') <div style="color:#b91c1c;margin-top:8px;">{{ $message }}</div> @enderror

        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit"
                    style="background:#2CBFAE;color:white;border:none;padding:12px 20px;border-radius:6px;font-weight:600;cursor:pointer;">
                Save Changes
            </button>

            <a href="{{ url('/app/sharpfleet/admin/vehicles') }}"
               style="background:#e5e7eb;color:#111827;padding:12px 20px;border-radius:6px;text-decoration:none;font-weight:600;">
                Cancel
            </a>
        </div>

    </form>

</div>

@endsection
