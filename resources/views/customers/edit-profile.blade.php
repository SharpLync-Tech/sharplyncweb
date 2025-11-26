{{-- Page: customers/profile/edit.blade.php
     Version: v2.1 (Standard Places Autocomplete + Back Button)
--}}

@extends('customers.layouts.customer-layout')

@section('title', 'Edit Profile')

@push('styles')
    <link rel="stylesheet" href="{{ secure_asset('css/customer-profile-edit.css') }}">
@endpush

@section('content')
<div class="cp-edit-card">

    {{-- 🔹 BACK TO PORTAL BUTTON --}}
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ url('/portal') }}" class="cp-edit-back-btn">
            ← Back to Portal
        </a>
    </div>

    <h2 class="cp-edit-title">Edit Your Profile</h2>

    <form method="POST" action="{{ route('customer.profile.update') }}" enctype="multipart/form-data">
        @csrf

        {{-- AVATAR --}}
        <div class="cp-edit-avatar-row">
            <div class="cp-edit-avatar">
                @php
                    $photo = $user->profile_photo ?? null;
                    $initials = strtoupper(
                        substr($user->first_name ?? 'U', 0, 1) .
                        substr($user->last_name ?? 'X', 0, 1)
                    );
                @endphp

                @if($photo)
                    <img src="{{ asset('storage/' . $photo) }}" alt="Avatar">
                @else
                    <span>{{ $initials }}</span>
                @endif
            </div>

            <div class="cp-edit-avatar-info">
                <label>Profile Photo</label>
                <input type="file" name="profile_photo" accept="image/*">
                <p style="font-size: .85rem; color: #6b7a89;">Max 2MB · JPG, PNG, WebP</p>
            </div>
        </div>

        {{-- BUSINESS NAME --}}
        <div class="cp-field">
            <label>Business Name</label>
            <input
                type="text"
                name="business_name"
                value="{{ old('business_name', $profile->business_name) }}"
                required
            >
        </div>

        {{-- MOBILE --}}
        <div class="cp-field">
            <label>Mobile Number</label>
            <input
                type="text"
                name="mobile_number"
                value="{{ old('mobile_number', $profile->mobile_number) }}"
                required
            >
        </div>

        {{-- STREET ADDRESS --}}
        <div class="cp-field">
            <label>Street Address</label>
            <input
                type="text"
                id="address_line1"
                name="address_line1"
                placeholder="Start typing your address…"
                value="{{ old('address_line1', $profile->address_line1) }}"
                autocomplete="street-address"
            >
        </div>

        {{-- CITY + STATE --}}
        <div class="cp-grid-2">
            <div class="cp-field">
                <label>City / Suburb</label>
                <input
                    type="text"
                    id="city"
                    name="city"
                    value="{{ old('city', $profile->city) }}"
                >
            </div>

            <div class="cp-field">
                <label>State</label>
                <input
                    type="text"
                    id="state"
                    name="state"
                    value="{{ old('state', $profile->state) }}"
                >
            </div>
        </div>

        {{-- POSTCODE + COUNTRY --}}
        <div class="cp-grid-2">
            <div class="cp-field">
                <label>Postcode</label>
                <input
                    type="text"
                    id="postcode"
                    name="postcode"
                    value="{{ old('postcode', $profile->postcode) }}"
                >
            </div>

            <div class="cp-field">
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

        <button class="cp-edit-submit" type="submit">Save Changes</button>
    </form>

</div>
@endsection

@push('scripts')
    {{-- Google Maps JS --}}
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&callback=initProfileAutocomplete">
    </script>

    <script>
        // Google Places callback
        window.initProfileAutocomplete = function () {
            console.log('[PROFILE] initProfileAutocomplete fired');

            const addressInput = document.getElementById('address_line1');
            const cityInput     = document.getElementById('city');
            const stateInput    = document.getElementById('state');
            const postcodeInput = document.getElementById('postcode');
            const countryInput  = document.getElementById('country');

            const ac = new google.maps.places.Autocomplete(addressInput, {
                fields: ['formatted_address', 'address_components'],
                componentRestrictions: { country: ['au', 'nz'] }
            });

            ac.addListener('place_changed', () => {
                const place = ac.getPlace();
                if (!place.address_components) return;

                const comps = place.address_components;
                const pick = type => {
                    const c = comps.find(x => x.types.includes(type));
                    return c ? c.long_name : '';
                };

                addressInput.value = place.formatted_address || addressInput.value;
                cityInput.value    = pick('locality') || pick('postal_town') || cityInput.value;
                stateInput.value   = pick('administrative_area_level_1') || stateInput.value;
                postcodeInput.value = pick('postal_code') || postcodeInput.value;

                const countryName = pick('country');
                if (countryName === 'Australia' || countryName === 'New Zealand') {
                    countryInput.value = countryName;
                } else {
                    countryInput.value = 'Other';
                }
            });
        };
    </script>
@endpush
