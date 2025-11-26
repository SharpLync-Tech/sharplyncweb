@extends('customers.layouts.customer-layout')

@section('title', 'Edit Profile | SharpLync')

@section('content')
<section class="content-card fade-in">

  {{-- DEBUG BANNER --}}
  <div style="background:#ffe4e4; padding:10px; border:2px solid red; margin-bottom:20px;">
      <strong>DEBUG:</strong> This is the STEP 3 DEBUG VERSION of edit-profile.blade.php (env API key)
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

    {{-- STREET ADDRESS (Google autocomplete target) --}}
    <label>Street Address</label>
    <input type="text"
           id="address_autocomplete"
           name="address_line1"
           value="{{ old('address_line1', $profile->address_line1) }}"
           placeholder="Start typing your address..."
           required>

    {{-- CITY, STATE --}}
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

<script>
console.log("DEBUG: Scripts block loaded");

document.addEventListener("DOMContentLoaded", function () {
    console.log("DEBUG: DOM ready");

    var input = document.getElementById('address_autocomplete');
    console.log("DEBUG: Input exists?", input);

    if (!input) {
        console.log("DEBUG: No autocomplete input found");
        return;
    }

    try {
        console.log("DEBUG: Google object:", typeof google);
        console.log("DEBUG: google.maps:", google && google.maps);
        console.log("DEBUG: google.maps.places:", google && google.maps && google.maps.places);

        var autocomplete = new google.maps.places.Autocomplete(input, {
            componentRestrictions: { country: ['au', 'nz'] },
            fields: ['address_components']
        });

        console.log("DEBUG: Autocomplete created");

        autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();
            console.log("DEBUG: place_changed triggered");
            console.log(place);

            if (!place.address_components) {
                console.log("DEBUG: No address components returned");
                return;
            }

            function find(type) {
                var comp = place.address_components.find(function(c) {
                    return c.types.indexOf(type) !== -1;
                });
                return comp ? comp.long_name : "";
            }

            document.getElementById('city').value     = find('locality');
            document.getElementById('state').value    = find('administrative_area_level_1');
            document.getElementById('postcode').value = find('postal_code');
            document.getElementById('country').value  = find('country');
        });

    } catch (err) {
        console.error("DEBUG: Autocomplete error:", err);
    }
});
</script>

{{-- GOOGLE MAPS API WITH ENV VARIABLE --}}
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places"
        onload="console.log('DEBUG: Google JS Loaded')"
        onerror="console.error('DEBUG: Google JS FAILED')"></script>

@endsection
