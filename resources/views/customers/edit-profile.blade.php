@extends('layouts.content')

@section('title', 'Edit Profile | SharpLync')

@section('content')
<section class="content-card fade-in">
  <h2>Edit Your Profile</h2>

  <form method="POST" action="{{ route('customer.profile.update') }}" enctype="multipart/form-data">
    @csrf

    {{-- AVATAR UPLOAD SECTION --}}
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
            <label class="fw-bold">Profile Photo</label>
            <input type="file" name="profile_photo" accept="image/*">
            <p style="font-size: 0.85rem; color: #777; margin-top: 4px;">
                Max 2MB · JPG, PNG, WebP
            </p>
        </div>
    </div>

    <hr class="mb-4">

    {{-- EXISTING FIELDS --}}
    <label>Business Name</label>
    <input type="text" name="business_name" 
           value="{{ old('business_name', $profile->business_name) }}" required>

    <label>Mobile Number</label>
    <input type="text" name="mobile_number" 
           value="{{ old('mobile_number', $profile->mobile_number) }}" required>

    <label>Address</label>
    <input type="text" name="address_line1" 
           value="{{ old('address_line1', $profile->address_line1) }}" required>

    <label>Postcode</label>
    <input type="text" name="postcode" 
           value="{{ old('postcode', $profile->postcode) }}" required>

    <button type="submit" class="btn-primary">Save Changes</button>
  </form>
</section>
@endsection
