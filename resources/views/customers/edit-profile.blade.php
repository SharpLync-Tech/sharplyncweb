{{-- 
 Page: customers/edit-profile.blade.php
 Version: 3.0
 - SharpLync Avatar Modal
 - Google Places Autocomplete
 - Clean layout, safe updates
--}}

@extends('customers.layouts.customer-layout')
@section('title', 'Edit Profile')

@push('styles')
<link rel="stylesheet" href="{{ secure_asset('css/customer-profile-edit.css') }}">
@endpush

@section('content')

<div class="cp-edit-card">

    <h2 class="cp-edit-title">Edit Your Profile</h2>

    {{-- =========================== --}}
    {{-- AVATAR & MODAL TRIGGER      --}}
    {{-- =========================== --}}
    <div class="cp-edit-avatar-row">
        <div class="cp-edit-avatar-wrapper">

            <div class="cp-edit-avatar">
                @php
                    $photo = $user->profile_photo ? asset('storage/'.$user->profile_photo) : null;
                    $initials = strtoupper(substr($user->first_name,0,1) . substr($user->last_name,0,1));
                @endphp

                @if($photo)
                    <img src="{{ $photo }}" alt="Avatar">
                @else
                    <span>{{ $initials }}</span>
                @endif
            </div>

            {{-- Pencil icon --}}
            <button id="open-avatar-modal" class="cp-avatar-edit-btn">
                ✎
            </button>

        </div>

        <div class="cp-edit-avatar-info">
            <label>Profile Photo</label>
        </div>
    </div>


    {{-- =========================== --}}
    {{-- EDIT FORM                  --}}
    {{-- =========================== --}}
    <form method="POST" action="{{ route('customer.profile.update') }}">
        @csrf

        <div class="cp-field">
            <label>Business Name</label>
            <input type="text" name="business_name"
                   value="{{ old('business_name', $profile->business_name) }}" required>
        </div>

        <div class="cp-field">
            <label>Mobile Number</label>
            <input type="text" name="mobile_number"
                   value="{{ old('mobile_number', $profile->mobile_number) }}" required>
        </div>

        <div class="cp-field">
            <label>Street Address</label>
            <input type="text" id="address_line1" name="address_line1"
                   placeholder="Start typing your address…"
                   value="{{ old('address_line1', $profile->address_line1) }}">
        </div>

        <div class="cp-grid-2">
            <div class="cp-field">
                <label>City / Suburb</label>
                <input id="city" name="city" type="text"
                       value="{{ old('city', $profile->city) }}">
            </div>

            <div class="cp-field">
                <label>State</label>
                <input id="state" name="state" type="text"
                       value="{{ old('state', $profile->state) }}">
            </div>
        </div>

        <div class="cp-grid-2">
            <div class="cp-field">
                <label>Postcode</label>
                <input id="postcode" name="postcode"
                       value="{{ old('postcode', $profile->postcode) }}">
            </div>

            <div class="cp-field">
                <label>Country</label>
                <select id="country" name="country">
                    @php $countryVal = old('country', $profile->country ?? 'Australia'); @endphp
                    <option value="Australia" {{ $countryVal=='Australia'?'selected':'' }}>Australia</option>
                    <option value="New Zealand" {{ $countryVal=='New Zealand'?'selected':'' }}>New Zealand</option>
                    <option value="Other" {{ $countryVal=='Other'?'selected':'' }}>Other</option>
                </select>
            </div>
        </div>

        <button class="cp-edit-submit" type="submit">Save Changes</button>
        <a href="/portal" class="cp-edit-cancel">Back to Portal</a>
    </form>

</div>


{{-- ====================================================== --}}
{{-- AVATAR MODAL                                           --}}
{{-- ====================================================== --}}
<div id="avatar-modal" class="cp-avatar-modal" aria-hidden="true">
    <div class="cp-avatar-modal-sheet">
        <header class="cp-avatar-modal-header">
            <h3>Update Profile Photo</h3>
            <button class="cp-avatar-modal-close">&times;</button>
        </header>

        <div class="cp-avatar-modal-body">

            <div class="cp-avatar-preview-box">
                <img id="avatar-preview" src="" alt="Preview">
                <p class="cp-avatar-hint">Max 2MB — JPG, PNG, WebP</p>
            </div>

            <input type="file" id="avatar-file-input" accept="image/*">

            <button id="avatar-save-btn" class="cp-btn cp-teal-btn" disabled>
                Save Photo
            </button>

            <button id="avatar-remove-btn" class="cp-btn cp-navy-btn">
                Remove Photo
            </button>

        </div>
    </div>
</div>

@endsection


@push('scripts')

{{-- Google Places --}}
<script async
        src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&callback=initProfileAutocomplete">
</script>

{{-- Avatar JS --}}
<script src="{{ secure_asset('js/profile-photo.js') }}"></script>

<script>
window.initProfileAutocomplete = function () {

    const addressInput = document.getElementById('address_line1');
    const cityEl = document.getElementById('city');
    const stateEl = document.getElementById('state');
    const pcEl = document.getElementById('postcode');
    const countryEl = document.getElementById('country');

    if (!addressInput || !google || !google.maps.places) return;

    const ac = new google.maps.places.Autocomplete(addressInput, {
        fields: ['address_components', 'formatted_address'],
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

        addressInput.value = place.formatted_address;
        cityEl.value = pick('locality') || pick('postal_town');
        stateEl.value = pick('administrative_area_level_1');
        pcEl.value = pick('postal_code');

        const ctry = pick('country');
        countryEl.value = (ctry === 'Australia' || ctry === 'New Zealand') ? ctry : 'Other';
    });
};
</script>

@endpush
