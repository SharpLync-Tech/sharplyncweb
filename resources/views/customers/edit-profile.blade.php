@extends('layouts.content')

@section('title', 'Edit Profile | SharpLync')

@section('content')
<section class="content-card fade-in">

  {{-- DEBUG BANNER --}}
  <div style="background:#ffe4e4; padding:10px; border:2px solid red; margin-bottom:20px;">
      <strong>DEBUG:</strong> This is the STEP 3 DEBUG VERSION of edit-profile.blade.php
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

    {{-- STREET ADDRESS --}}
    <label>Street Address</label>
    <input type="text"
           id="address_autocomplete"
           name="address_line1"
           value="{{ old('address_line1', $profile->address_line1) }}"
           placeholder="Start typing your address..."
           required>

    {{-- CITY, STATE, POSTCODE, COUNTRY (VISIBLE) --}}
    <div class="cp-grid-2" style="border:2px dotted blue; padding:10px; margin-top:10px;">
        <div>
            <label>City / Suburb (DEBUG visible)</label>
            <input type="text"
                   id="city"
                   name="city"
                   value="{{ old('city', $profile->city) }}">
        </div>

        <div>
            <label>State (DEBUG visible)</label>
            <input type="text"
                   id="state"
                   name="state"
                   value="{{ old('state', $profile->state) }}">
        </div>
    </div>

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
                <option value="New Zealand" {{ $country === 'New Zealand' ? 'selected' : '' }}>New Zealand</option>
                <option value="Other" {{ $country === 'Other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>
    </div>

    <button type="submit" class="btn-primary" style="margin-top:20px;">Save Changes</button>
  </form>
</section>
@endsection


{{-- DEBUG SCRIPTS --}}
@section('scripts')

<script>
console.log("DEBUG: scripts section reached (Blade @section loaded)");

document.addEventListener("DOMContentLoaded", function () {
    console.log("DEBUG: DOMContentLoaded fired");

    const input = document.getElementById("address_autocomplete");
    console.log("DEBUG: Address field present?", input);

    if (!input) {
        console.error("DEBUG: address_autocomplete NOT FOUND");
        return;
    }

    // Try loading Google Autocomplete
    try {
        console.log("DEBUG: google object exists?", typeof google !== 'undefined');
        console.log("DEBUG: google.maps exists?", google && google.maps);
        console.log("DEBUG: google.maps.places exists?", google && google.maps && google.maps.places);

        const autocomplete = new google.maps.places.Autocomplete(input, {
            componentRestrictions: { country: ["au", "nz"] },
            fields: ["address_components", "formatted_address"]
        });

        console.log("DEBUG: Autocomplete object created", autocomplete);

        autocomplete.addListener("place_changed", function () {
            console.log("DEBUG: place_changed fired");
            const place = autocomplete.getPlace();
            console.log("DEBUG: Place object:", place);

            if (!place.address_components) {
                console.error("DEBUG: NO address_components returned");
                return;
            }

            const getPart = (type) => {
                const comp = place.address_components.find(c => c.types.includes(type));
                return comp ? comp.long_name : "";
            };

            console.log("DEBUG: Extracted city:", getPart("locality"));
            console.log("DEBUG: Extracted state:", getPart("administrative_area_level_1"));
            console.log("DEBUG: Extracted postcode:", getPart("postal_code"));
            console.log("DEBUG: Extracted country:", getPart("country"));

            document.getElementById("city").value     = getPart("locality") || getPart("postal_town") || "";
            document.getElementById("state").value    = getPart("administrative_area_level_1") || "";
            document.getElementById("postcode").value = getPart("postal_code") || "";
            document.getElementById("country").value  = getPart("country") || "Australia";

            console.log("DEBUG: Fields updated successfully");
        });

    } catch (e) {
        console.error("DEBUG: ERROR initializing autocomplete", e);
    }
});
</script>

{{-- Google Maps API Loader --}}
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"
        onload="console.log('DEBUG: Google API LOADED')"
        onerror="console.error('DEBUG: Google API FAILED TO LOAD')"></script>

@endsection
