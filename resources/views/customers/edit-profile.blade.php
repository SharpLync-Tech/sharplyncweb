@extends('customers.layouts.customer-layout')

@section('title', 'Edit Profile')

@section('content')
<div class="cp-card profile-edit-card fade-in">

    <h2 class="cp-title">Edit Your Profile</h2>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="cp-alert success">{{ session('success') }}</div>
    @endif

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="cp-alert error">
            <ul>
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('customer.profile.update') }}" class="cp-form">
        @csrf

        {{-- Business / Mobile --}}
        <div class="cp-grid-2">
            <div class="cp-field">
                <label>Business Name</label>
                <input type="text" name="business_name"
                       value="{{ old('business_name', $profile->business_name) }}"
                       required>
            </div>

            <div class="cp-field">
                <label>Mobile Number</label>
                <input type="text" name="mobile_number"
                       value="{{ old('mobile_number', $profile->mobile_number) }}"
                       required>
            </div>
        </div>

        {{-- Address --}}
        <div class="cp-field">
            <label>Address</label>
            <input type="text" id="address-autocomplete"
                   name="address_line1"
                   value="{{ old('address_line1', $profile->address_line1) }}"
                   placeholder="Start typing your address..."
                   required>
        </div>

        {{-- Postcode --}}
        <div class="cp-grid-2">
            <div class="cp-field">
                <label>Postcode</label>
                <input type="text" name="postcode"
                       value="{{ old('postcode', $profile->postcode) }}"
                       required>
            </div>

            <div></div> {{-- empty column for alignment --}}
        </div>

        <button class="cp-btn-primary">Save Changes</button>
    </form>

</div>
@endsection

@section('scripts')
{{-- OPTIONAL — Google Places Autocomplete --}}
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('address-autocomplete');
    if (input) {
        new google.maps.places.Autocomplete(input, {
            componentRestrictions: { country: 'au' },
            fields: ['formatted_address']
        });
    }
});
</script>
@endsection
