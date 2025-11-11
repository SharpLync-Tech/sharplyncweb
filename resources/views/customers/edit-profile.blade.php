@extends('layouts.content')

@section('title', 'Edit Profile | SharpLync')

@section('content')
<section class="content-card fade-in">
  <h2>Edit Your Profile</h2>

  <form method="POST" action="{{ route('profile.update') }}">
    @csrf

    <label>Business Name</label>
    <input type="text" name="business_name" value="{{ old('business_name', $profile->business_name) }}" required>

    <label>Mobile Number</label>
    <input type="text" name="mobile_number" value="{{ old('mobile_number', $profile->mobile_number) }}" required>

    <label>Address</label>
    <input type="text" name="address_line1" value="{{ old('address_line1', $profile->address_line1) }}" required>

    <label>Postcode</label>
    <input type="text" name="postcode" value="{{ old('postcode', $profile->postcode) }}" required>

    <button type="submit" class="btn-primary">Save Changes</button>
  </form>
</section>
@endsection