@extends('customers.layouts.customer-layout')

@section('title', 'Edit Profile')

@section('content')
<div class="cp-card">

    <h2 style="margin-top:0; margin-bottom:1.5rem;">Edit Your Profile</h2>

    <form method="POST" action="{{ route('customer.profile.update') }}" enctype="multipart/form-data">
        @csrf

        {{-- AVATAR + PHOTO UPLOAD --}}
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
                <label>Profile Photo</label>
                <input type="file" name="profile_photo" accept="image/*">
                <p class="cp-avatar-hint">
                    Max 2MB · JPG, PNG, WebP
                </p>
            </div>
        </div>

        {{-- BUSINESS NAME --}}
        <div class="cp-field">
            <label for="business_name">Business Name</label>
            <input
                id="business_name"
                type="text"
                name="business_name"
                value="{{ old('business_name', $profile->business_name) }}"
                required
            >
        </div>

        {{-- MOBILE --}}
        <div class="cp-field">
            <label for="mobile_number">Mobile Number</label>
            <input
                id="mobile_number"
                type="text"
                name="mobile_number"
                value="{{ old('mobile_number', $profile->mobile_number) }}"
                required
            >
        </div>

        {{-- STREET ADDRESS WITH GOOGLE AUTOCOMPLETE --}}
        <div class="cp-field">
            <label for="address_autocomplete">Street Address</label>

            <gmpx-place-autocomplete
                id="address_autocomplete"
                placeholder="Start typing your address…"
                autocomplete="street-address"
            ></gmpx-place-autocomplete>

            {{-- hidden field actually saved to DB --}}
            <input
                type="hidden"
                id="address_line1"
                name="address_line1"
                value="{{ old('address_line1', $profile->address_line1) }}"
            >
        </div>

        {{-- CITY + STATE --}}
        <div class="cp-grid-2">
            <div class="cp-field">
                <label for="city">City / Suburb</label>
                <input
                    id="city"
                    type="text"
                    name="city"
                    value="{{ old('city', $profile->city) }}"
                >
            </div>

            <div class="cp-field">
                <label for="state">State</label>
                <input
                    id="state"
                    type="text"
                    name="state"
                    value="{{ old('state', $profile->state) }}"
                >
            </div>
        </div>

        {{-- POSTCODE + COUNTRY --}}
        <div class="cp-grid-2">
            <div class="cp-field">
                <label for="postcode">Postcode</label>
                <input
                    id="postcode"
                    type="text"
                    name="postcode"
                    value="{{ old('postcode', $profile->postcode) }}"
                    required
                >
            </div>

            <div class="cp-field">
                <label for="country">Country</label>
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

        <button type="submit" class="cp-btn-primary" style="margin-top:1.5rem;">
            Save Changes
        </button>
    </form>
</div>
@endsection


@section('scripts')
@php
    $mapsKey = env('GOOGLE_MAPS_API_KEY');
@endphp

@if ($mapsKey)
    {{-- REQUIRED: Load Google Maps JS FIRST (no async/defer) --}}
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&libraries=places&v=weekly"></script>

    {{-- Extended Component Library for <gmpx-place-autocomplete> --}}
    <script type="module" src="https://unpkg.com/@googlemaps/extended-component-library@0.6.1"></script>

    <script>
        window.addEventListener("load", function () {
            const ac        = document.getElementById("address_autocomplete");
            const hidden    = document.getElementById("address_line1");
            const cityEl    = document.getElementById("city");
            const stateEl   = document.getElementById("state");
            const pcEl      = document.getElementById("postcode");
            const countryEl = document.getElementById("country");

            if (!ac || !hidden) return;

            // If we already have an address, show it in the visible element
            if (hidden.value) {
                ac.value = hidden.value;
            }

            ac.addEventListener("gmpx-placechange", () => {
                const selectedAddress = ac.value || "";
                hidden.value = selectedAddress;

                if (!window.google || !google.maps) return;

                const geocoder = new google.maps.Geocoder();

                geocoder.geocode({ address: selectedAddress }, (results, status) => {
                    if (status !== "OK" || !results[0]) return;

                    const comps = results[0].address_components || [];

                    const find = (type) => {
                        const c = comps.find(x => x.types.includes(type));
                        return c ? c.long_name : "";
                    };

                    if (cityEl)    cityEl.value    = find("locality") || find("postal_town") || "";
                    if (stateEl)   stateEl.value   = find("administrative_area_level_1") || "";
                    if (pcEl)      pcEl.value      = find("postal_code") || "";
                    if (countryEl) countryEl.value = find("country") || "Australia";
                });
            });
        });
    </script>
@else
    <script>
        console.warn("GOOGLE_MAPS_API_KEY missing — address autocomplete disabled.");
    </script>
@endif
@endsection
