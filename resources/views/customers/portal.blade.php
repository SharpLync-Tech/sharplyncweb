{{-- 
    Page: customers/profile/edit.blade.php
    Version: v2.1 (Standard Places Autocomplete + Avatar Modal Update)
--}}

@extends('customers.layouts.customer-layout')

@section('title', 'Edit Profile')

@push('styles')
<link rel="stylesheet" href="{{ secure_asset('css/customer-profile-edit.css') }}">

<style>
    /* === Avatar Pencil Icon Overlay === */
    .cp-edit-avatar-wrapper {
        position: relative;
        display: inline-block;
    }

    .cp-avatar-pencil {
        position: absolute;
        bottom: 4px;
        right: 4px;
        background: #0A2A4D;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 3px 6px rgba(0,0,0,0.25);
        transition: background .2s ease;
    }
    .cp-avatar-pencil:hover {
        background: #104976;
    }

    .cp-avatar-pencil svg {
        width: 16px;
        height: 16px;
        fill: white;
    }

    /* === Photo Upload Modal === */
    #cp-photo-modal {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    #cp-photo-modal.cp-modal-visible {
        display: flex;
    }

    .cp-photo-sheet {
        background: #fff;
        width: 380px;
        padding: 1.5rem;
        border-radius: 14px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.25);
        animation: fadeInUp .25s ease;
    }

    @keyframes fadeInUp {
        from { opacity:0; transform: translateY(20px); }
        to { opacity:1; transform: translateY(0); }
    }

    .cp-photo-title {
        font-size: 1.3rem;
        margin-bottom: .5rem;
        color: #0A2A4D;
    }

    .cp-photo-preview {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        margin: 1rem auto;
        display: block;
        object-fit: cover;
        border: 3px solid #eee;
    }
</style>
@endpush

@section('content')
<div class="cp-edit-card">

    <h2 class="cp-edit-title">Edit Your Profile</h2>

    <form method="POST" action="{{ route('customer.profile.update') }}" enctype="multipart/form-data">
        @csrf

        {{-- AVATAR --}}
        <div class="cp-edit-avatar-row">

            <div class="cp-edit-avatar-wrapper">
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

                {{-- Pencil Icon --}}
                <div class="cp-avatar-pencil" id="open-photo-modal">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM21.41 
                                 6.34c.38-.38.38-1.02 0-1.41l-2.34-2.34a1 
                                 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 
                                 1.83-1.83z"/>
                    </svg>
                </div>
            </div>

            <div class="cp-edit-avatar-info">
                <label>Profile Photo</label>
                <p style="font-size: .85rem; color: #6b7a89;">Max 2MB · JPG, PNG, WebP</p>
            </div>
        </div>

        {{-- BUSINESS NAME --}}
        <div class="cp-field">
            <label>Business Name</label>
            <input type="text" name="business_name"
                   value="{{ old('business_name', $profile->business_name) }}" required>
        </div>

        {{-- MOBILE --}}
        <div class="cp-field">
            <label>Mobile Number</label>
            <input type="text" name="mobile_number"
                   value="{{ old('mobile_number', $profile->mobile_number) }}" required>
        </div>

        {{-- ADDRESS --}}
        <div class="cp-field">
            <label>Street Address</label>
            <input type="text" id="address_line1" name="address_line1"
                   placeholder="Start typing your address…"
                   value="{{ old('address_line1', $profile->address_line1) }}">
        </div>

        <div class="cp-grid-2">
            <div class="cp-field">
                <label>City / Suburb</label>
                <input type="text" id="city" name="city" value="{{ old('city', $profile->city) }}">
            </div>

            <div class="cp-field">
                <label>State</label>
                <input type="text" id="state" name="state" value="{{ old('state', $profile->state) }}">
            </div>
        </div>

        <div class="cp-grid-2">
            <div class="cp-field">
                <label>Postcode</label>
                <input type="text" id="postcode" name="postcode" value="{{ old('postcode', $profile->postcode) }}">
            </div>

            <div class="cp-field">
                <label>Country</label>
                @php $country = old('country', $profile->country ?? 'Australia'); @endphp
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

{{-- =============================== --}}
{{-- PROFILE PHOTO UPLOAD MODAL     --}}
{{-- =============================== --}}
<div id="cp-photo-modal">
    <div class="cp-photo-sheet">

        <h3 class="cp-photo-title">Update Profile Photo</h3>

        @if($photo)
            <img src="{{ asset('storage/' . $photo) }}" class="cp-photo-preview" id="photo-preview">
        @else
            <img src="https://placehold.co/100x100?text=Avatar" class="cp-photo-preview" id="photo-preview">
        @endif

        <form method="POST" action="{{ route('customer.profile.update') }}" enctype="multipart/form-data">
            @csrf

            <input type="file" name="profile_photo" accept="image/*" style="margin-bottom:1rem;" required>

            <div style="display:flex; justify-content:flex-end; gap:.75rem;">
                <button type="button" id="close-photo-modal" class="cp-btn cp-navy-btn">Cancel</button>
                <button type="submit" class="cp-btn cp-teal-btn">Upload</button>
            </div>
        </form>

    </div>
</div>

@endsection

@push('scripts')

{{-- Google Maps Autocomplete --}}
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&callback=initProfileAutocomplete">
</script>

<script>
/* Avatar modal controls */
const photoModal = document.getElementById('cp-photo-modal');
document.getElementById('open-photo-modal').onclick = () =>
    photoModal.classList.add('cp-modal-visible');

document.getElementById('close-photo-modal').onclick = () =>
    photoModal.classList.remove('cp-modal-visible');

/* Background click closes modal */
photoModal.addEventListener('click', e => {
    if (e.target === photoModal) {
        photoModal.classList.remove('cp-modal-visible');
    }
});
</script>

{{-- Google Places Autocomplete --}}
<script>
window.initProfileAutocomplete = function () {
    const addressInput  = document.getElementById('address_line1');
    const cityInput     = document.getElementById('city');
    const stateInput    = document.getElementById('state');
    const postcodeInput = document.getElementById('postcode');
    const countryInput  = document.getElementById('country');

    const autocomplete = new google.maps.places.Autocomplete(addressInput, {
        fields: ['address_components','formatted_address'],
        componentRestrictions: { country: ['au', 'nz'] }
    });

    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (!place.address_components) return;

        const part = type => {
            const c = place.address_components.find(x => x.types.includes(type));
            return c ? c.long_name : '';
        };

        addressInput.value = place.formatted_address;
        cityInput.value     = part('locality') || part('postal_town');
        stateInput.value    = part('administrative_area_level_1');
        postcodeInput.value = part('postal_code');

        const country = part('country');
        countryInput.value = country === 'Australia' || country === 'New Zealand'
            ? country
            : 'Other';
    });
};
</script>

@endpush
