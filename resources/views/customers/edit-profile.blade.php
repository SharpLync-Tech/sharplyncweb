@extends('layouts.content')

@section('title', 'Edit Profile | SharpLync')

@section('content')
<section class="content-card fade-in">
  <h2>Edit Your Profile</h2>

  <form method="POST" action="{{ route('customer.profile.update') }}" enctype="multipart/form-data">
    @csrf

    {{-- AVATAR UPLOAD SECTION --}}
    <div class="cp-avatar-row">
        <div class="cp-avatar-large">
            @php
                $photo = $user->profile_photo ?? null;
                $initials = strtoupper(
                    substr($user->first_name ?? 'U', 0, 1) .
                    substr($user->last_name ?? '', 0, 1)
                );
            @endphp

            @if($photo)
                <img src="{{ asset('storage/' . $photo) }}" alt="Avatar">
            @else
                <span>{{ $initials }}</span>
            @endif
        </div>

        <div class="cp-avatar-actions">
            <label class="fw-bold">Profile Photo</label>
            <input type="file" name="profile_photo" accept="image/*">
            <p style="font-size: 0.85rem; color: #777; margin-top: 4px;">
                Max 2MB · JPG, PNG, WebP
            </p>
        </div>
    </div>

    <hr class="mb-4">

    {{-- EXISTING FIELDS WITH AUTOCOMPLETE IDs --}}
    <label>Business Name</label>
    <input type="text" name="business_name"
           value="{{ old('business_name', $profile->business_name) }}" required>

    <label>Mobile Number</label>
    <input type="text" name="mobile_number"
           value="{{ old('mobile_number', $profile->mobile_number) }}" required>

    <label>Address</label>
    <input type="text"
           id="address_autocomplete"
           name="address_line1"
           value="{{ old('address_line1', $profile->address_line1) }}"
           placeholder="Start typing your address..."
           required>

    <label>Postcode</label>
    <input type="text"
           id="postcode"
           name="postcode"
           value="{{ old('postcode', $profile->postcode) }}"
           required>

    {{-- NEW auto-fill fields --}}
    <input type="hidden" id="city" name="city" value="{{ old('city', $profile->city) }}">
    <input type="hidden" id="state" name="state" value="{{ old('state', $profile->state) }}">
    <input type="hidden" id="country" name="country" value="{{ old('country', $profile->country ?? 'Australia') }}">

    <button type="submit" class="btn-primary">Save Changes</button>
  </form>
</section>
@endsection

@section('scripts')
{{-- STEP 2 — Google Places Autocomplete --}}
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("address_autocomplete");
    if (!input) return;

    const autocomplete = new google.maps.places.Autocomplete(input, {
        componentRestrictions: { country: ["au", "nz"] },
        fields: ["address_components", "formatted_address"]
    });

    autocomplete.addListener("place_changed", function () {
        const place = autocomplete.getPlace();
        if (!place.address_components) return;

        const getPart = (type) => {
            const comp = place.address_components.find(c => c.types.includes(type));
            return comp ? comp.long_name : "";
        };

        // Auto-fill hidden fields
        document.getElementById("city").value     = getPart("locality") || getPart("postal_town") || "";
        document.getElementById("state").value    = getPart("administrative_area_level_1") || "";
        document.getElementById("postcode").value = getPart("postal_code") || "";
        document.getElementById("country").value  = getPart("country") || "Australia";
    });
});
</script>
@endsection
