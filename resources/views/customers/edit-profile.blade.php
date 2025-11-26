

@section('title', 'Edit Profile')
<link rel="stylesheet" href="/css/customer-profile-edit.css">
<link rel="stylesheet" href="/css/google-places.css">
@push('styles')
    <link rel="stylesheet" href="{{ secure_asset('css/customer-profile-edit.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('css/google-places.css') }}">
@endpush

@section('content')
<div class="cp-edit-card">

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
            <input type="text"
                   name="business_name"
                   value="{{ old('business_name', $profile->business_name) }}"
                   required>
        </div>

        {{-- MOBILE --}}
        <div class="cp-field">
            <label>Mobile Number</label>
            <input type="text"
                   name="mobile_number"
                   value="{{ old('mobile_number', $profile->mobile_number) }}"
                   required>
        </div>

        {{-- ADDRESS --}}
        <div class="cp-field">
            <label>Street Address</label>
            <gmpx-place-autocomplete
                id="address_autocomplete"
                placeholder="Start typing your address…"
                autocomplete="street-address"
            ></gmpx-place-autocomplete>

            <input type="hidden"
                   id="address_line1"
                   name="address_line1"
                   value="{{ old('address_line1', $profile->address_line1) }}">
        </div>

        {{-- CITY + STATE --}}
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

        {{-- POSTCODE + COUNTRY --}}
        <div class="cp-grid-2">
            <div class="cp-field">
                <label>Postcode</label>
                <input id="postcode" name="postcode" type="text"
                       value="{{ old('postcode', $profile->postcode) }}">
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

        {{-- SUBMIT --}}
        <button class="cp-edit-submit" type="submit">Save Changes</button>
    </form>

</div>
@endsection

@push('scripts')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&v=weekly"></script>
    <script type="module" src="https://unpkg.com/@googlemaps/extended-component-library@0.6.1"></script>
    <script src="{{ secure_asset('js/profile-edit.js') }}"></script>
@endpush
