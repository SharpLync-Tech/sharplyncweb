@extends('customers.layouts.customer-layout')

@section('title', 'Edit Profile | SharpLync')

@section('content')
<section class="content-card fade-in">

  {{-- DEBUG BANNER --}}
  <div style="background:#ffe4e4; padding:10px; border:2px solid red; margin-bottom:20px;">
      <strong>DEBUG:</strong> Using NEW Google PlaceAutocompleteElement (2025 API)
  </div>

  <h2>Edit Your Profile</h2>

  <form method="POST" action="{{ route('customer.profile.update') }}" enctype="multipart/form-data">
    @csrf

    {{-- AVATAR UPLOAD SECTION --}}
    <div class="cp-avatar-row" style="border: 2px dashed red; padding: 10px;">
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

    {{-- BUSINESS NAME --}}
    <label>Business Name</label>
    <input type="text" name="business_name"
           value="{{ old('business_name', $profile->business_name) }}" required>

    {{-- MOBILE NUMBER --}}
    <label>Mobile Number</label>
    <input type="text" name="mobile_number"
           value="{{ old('mobile_number', $profile->mobile_number) }}" required>

    {{-- GOOGLE PLACE AUTOCOMPLETE ELEMENT --}}
    <label>Street Address</label>

    <gmpx-place-autocomplete
        id="address_autocomplete"
        placeholder="Start typing your address..."
        style="width:100%; padding:12px; border:1px solid #dce3ea; border-radius:10px; background:#fff;"
    ></gmpx-place-autocomplete>

    {{-- Hidden actual address field --}}
    <input type="hidden"
           id="address_line1"
           name="address_line1"
           value="{{ old('address_line1', $profile->address_line1) }}">

    {{-- CITY, STATE --}}
    <div class="cp-grid-2" style="border:2px dotted blue; padding:10px; margin-top:10px;">
        <div>
            <label>City / Suburb</label>
            <input type="text"
                   id="city"
                   name="city"
                   value="{{ old('city', $profile->city) }}">
        </div>

        <div>
            <label>State</label>
            <input type="text"
                   id="state"
                   name="state"
                   value="{{ old('state', $profile->state) }}">
        </div>
    </div>

    {{-- POSTCODE / COUNTRY --}}
    <div class="cp-grid-2" style="border:2px dotted green; padding:10px; margin-top:10px;">
        <div>
            <label>Postcode</label>
            <input type="text"
                   id="postcode"
                   name="postcode"
                   value="{{ old('postcode', $profile->postcode) }}"
                   required>
        </div>

        <div>
            <label>Country</label>
            @php
                $country = old('country', $profile->country ?? 'Australia');
            @endphp
            <select id="country" name="country">
                <option value="Australia" {{ $country === 'Australia' ? 'selected' : '' }}>Australia</option>
                <option value="New Zealand" {{ strtolower($country) === 'new zealand' ? 'selected' : '' }}>New Zealand</option>
                <option value="Other" {{ $country === 'Other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>
    </div>

    <button type="submit" class="btn-primary" style="margin-top:20px;">Save Changes</button>

  </form>
</section>
@endsection

@section('scripts')

{{-- LOAD GOOGLE MAPS JS --}}
<script 
    src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&v=weekly"
    defer
></script>

{{-- LOAD EXTENDED COMPONENTS (REQUIRED for <gmpx-place-autocomplete>) --}}
<script 
    type="module"
    src="https://unpkg.com/@googlemaps/extended-component-library@0.6.1"
></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    console.log("DEBUG: DOM loaded (new Google PlaceAutocompleteElement)");

    const ac = document.getElementById("address_autocomplete");
    const addressField = document.getElementById("address_line1");

    if (!ac) {
        console.error("DEBUG: Autocomplete element missing");
        return;
    }

    ac.addEventListener("gmpx-placechange", () => {
        const placeString = ac.value;
        console.log("DEBUG: PLACE CHANGED:", placeString);

        // Save full formatted address
        addressField.value = placeString;

        // Geocode to extract components
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ address: placeString }, function (results, status) {
            if (status === "OK" && results[0]) {
                const comps = results[0].address_components;

                function find(type) {
                    const c = comps.find(x => x.types.includes(type));
                    return c ? c.long_name : "";
                }

                document.getElementById("city").value     = find("locality") || find("postal_town") || "";
                document.getElementById("state").value    = find("administrative_area_level_1") || "";
                document.getElementById("postcode").value = find("postal_code") || "";
                document.getElementById("country").value  = find("country") || "Australia";

                console.log("DEBUG: Autofilled city/state/postcode");
            } else {
                console.error("DEBUG: Geocoder failed:", status);
            }
        });
    });

});
</script>

@endsection

