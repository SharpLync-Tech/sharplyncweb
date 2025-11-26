{{-- 
    Page: customers/profile/edit.blade.php
    Version: v3.0 (Avatar Modal + Cropping + Live Preview + Remove)
--}}

@extends('customers.layouts.customer-layout')

@section('title', 'Edit Profile')

@push('styles')
<link rel="stylesheet" href="{{ secure_asset('css/customer-profile-edit.css') }}">

{{-- CropperJS --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">

<style>
/* ================================
   AVATAR + PENCIL ICON
================================ */
.cp-edit-avatar-wrapper {
    position: relative;
    display: inline-block;
}

.cp-avatar-pencil {
    position: absolute;
    bottom: 4px;
    right: 4px;
    background: #2CBFAE; /* SharpLync teal */
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
    background: #24a796;
}

.cp-avatar-pencil svg {
    width: 16px;
    height: 16px;
    fill: white;
}

/* ================================
   MODAL STYLES
================================ */
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
    width: 450px;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 20px 40px rgba(0,0,0,.25);
    overflow: hidden;
}

.cp-photo-header {
    background: #0A2A4D; /* navy */
    padding: 1rem 1.5rem;
    color: white;
}

.cp-photo-body {
    padding: 1.3rem 1.5rem;
}

.cp-photo-live {
    width: 260px;
    height: 260px;
    border-radius: 100%;
    margin: 0 auto 1.5rem;
    overflow: hidden;
    border: 4px solid #e8eef5;
}

.cp-photo-actions {
    display: flex;
    justify-content: flex-end;
    gap: .75rem;
    margin-top: 1rem;
}

/* Buttons */
.cp-btn-teal {
    background: #2CBFAE;
    color: white;
    padding: .6rem 1.1rem;
    border-radius: 8px;
}
.cp-btn-teal:hover {
    background: #24a796;
}

.cp-btn-navy {
    background: #0A2A4D;
    color: white;
    padding: .6rem 1.1rem;
    border-radius: 8px;
}
.cp-btn-navy:hover {
    background: #104976;
}

.cp-btn-danger {
    background: #b3261e;
    color: white;
    padding: .6rem 1.1rem;
    border-radius: 8px;
}
.cp-btn-danger:hover {
    background: #8e1d18;
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
                        <img src="{{ asset('storage/' . $photo) }}" id="current-avatar" alt="Avatar">
                    @else
                        <span id="current-avatar-text">{{ $initials }}</span>
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
                {{-- removed text --}}
            </div>
        </div>

        {{-- ======================= --}}
        {{-- PROFILE FORM FIELDS     --}}
        {{-- ======================= --}}

        <div class="cp-field">
            <label>Business Name</label>
            <input type="text" name="business_name" value="{{ old('business_name', $profile->business_name) }}" required>
        </div>

        <div class="cp-field">
            <label>Mobile Number</label>
            <input type="text" name="mobile_number" value="{{ old('mobile_number', $profile->mobile_number) }}" required>
        </div>

        <div class="cp-field">
            <label>Street Address</label>
            <input id="address_line1" name="address_line1" type="text"
                   value="{{ old('address_line1', $profile->address_line1) }}">
        </div>

        <div class="cp-grid-2">
            <div class="cp-field">
                <label>City / Suburb</label>
                <input id="city" name="city" type="text" value="{{ old('city', $profile->city) }}">
            </div>

            <div class="cp-field">
                <label>State</label>
                <input id="state" name="state" type="text" value="{{ old('state', $profile->state) }}">
            </div>
        </div>

        <div class="cp-grid-2">
            <div class="cp-field">
                <label>Postcode</label>
                <input id="postcode" name="postcode" type="text" value="{{ old('postcode', $profile->postcode) }}">
            </div>

            <div class="cp-field">
                <label>Country</label>
                <select id="country" name="country">
                    @php $c = old('country', $profile->country ?? 'Australia') @endphp
                    <option value="Australia" {{ $c === 'Australia' ? 'selected':'' }}>Australia</option>
                    <option value="New Zealand" {{ $c === 'New Zealand' ? 'selected':'' }}>New Zealand</option>
                    <option value="Other" {{ $c === 'Other' ? 'selected':'' }}>Other</option>
                </select>
            </div>
        </div>

        <button class="cp-edit-submit" type="submit">Save Changes</button>
    </form>

</div>

{{-- =============================== --}}
{{-- PHOTO MODAL                    --}}
{{-- =============================== --}}
<div id="cp-photo-modal">
    <div class="cp-photo-sheet">

        <div class="cp-photo-header">
            <h3>Update Profile Photo</h3>
        </div>

        <div class="cp-photo-body">

            <div class="cp-photo-live">
                <img id="cropper-image" style="width:100%; display:none;">
            </div>

            <input type="file" id="photo-input" accept="image/*" style="margin-bottom:1rem;">

            <div class="cp-photo-actions">

                @if($photo)
                <form method="POST" action="{{ route('customer.profile.remove-photo') }}">
                    @csrf
                    <button type="submit" class="cp-btn-danger">Remove Photo</button>
                </form>
                @endif

                <button class="cp-btn-navy" id="close-photo-modal">Cancel</button>
                <button class="cp-btn-teal" id="save-cropped-photo">Save</button>
            </div>

        </div>

    </div>
</div>

@endsection

@push('scripts')

{{-- Google Maps --}}
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&callback=initProfileAutocomplete">
</script>

{{-- CropperJS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
/* ================================
   Avatar Modal Logic
================================ */
const modal = document.getElementById('cp-photo-modal');
const openBtn = document.getElementById('open-photo-modal');
const closeBtn = document.getElementById('close-photo-modal');

openBtn.onclick = () => modal.classList.add('cp-modal-visible');
closeBtn.onclick = () => modal.classList.remove('cp-modal-visible');

modal.addEventListener('click', e => {
    if (e.target === modal) modal.classList.remove('cp-modal-visible');
});

/* ================================
   CropperJS + Live Preview
================================ */
let cropper;
const img = document.getElementById('cropper-image');
const input = document.getElementById('photo-input');

input.addEventListener('change', e => {
    const file = e.target.files[0];
    if (!file) return;

    const url = URL.createObjectURL(file);
    img.src = url;
    img.style.display = 'block';

    if (cropper) cropper.destroy();

    cropper = new Cropper(img, {
        aspectRatio: 1,
        viewMode: 1,
        background: false,
        dragMode: 'move',
        autoCropArea: 1
    });
});

/* ================================
   Save Cropped Photo to server
================================ */
document.getElementById('save-cropped-photo')
    .addEventListener('click', function () {

        if (!cropper) return alert("Please select an image first");

        cropper.getCroppedCanvas({
            width: 600,
            height: 600
        }).toBlob(blob => {

            const form = new FormData();
            form.append("profile_photo", blob, "avatar.jpg");
            form.append("_token", "{{ csrf_token() }}");

            fetch("{{ route('customer.profile.update-photo') }}", {
                method: "POST",
                body: form
            })
            .then(() => location.reload());
        });
    });

/* ================================
   Google Autocomplete
================================ */
window.initProfileAutocomplete = function () {
    const addressInput  = document.getElementById('address_line1');
    const cityInput     = document.getElementById('city');
    const stateInput    = document.getElementById('state');
    const postcodeInput = document.getElementById('postcode');
    const countryInput  = document.getElementById('country');

    const ac = new google.maps.places.Autocomplete(addressInput, {
        fields: ['address_components','formatted_address'],
        componentRestrictions: { country: ['au','nz'] }
    });

    ac.addListener('place_changed', () => {
        const p = ac.getPlace();
        if (!p.address_components) return;

        const part = t => {
            const c = p.address_components.find(x => x.types.includes(t));
            return c ? c.long_name : '';
        };

        addressInput.value = p.formatted_address;
        cityInput.value     = part('locality') || part('postal_town');
        stateInput.value    = part('administrative_area_level_1');
        postcodeInput.value = part('postal_code');

        const countryName = part('country');
        countryInput.value = ['Australia','New Zealand'].includes(countryName)
            ? countryName : 'Other';
    });
};
</script>

@endpush
