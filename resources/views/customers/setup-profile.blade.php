{{-- 
  Page: customers/setup-profile.blade.php
  Version: v2.1 (Clean markup, class-based)
  Description: Step 4 – Collects customer contact details and security PIN
--}}

@extends('layouts.base')

@section('title', 'Complete Your Profile')

@section('content')
<section class="onboard-container">
  <div class="onboard-card">

    <h1>Complete Your Profile</h1>
    <p class="onboard-subtitle">
      Just a few more details so we can personalise your SharpLync experience.
    </p>

    {{-- success / error alerts --}}
    @if(session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($errors->any())
      <div class="alert alert-error">
        @foreach ($errors->all() as $error)
          <p>{{ $error }}</p>
        @endforeach
      </div>
    @endif

    {{-- profile form --}}
    <form method="POST" action="{{ route('profile.store') }}" class="onboard-form">
      @csrf

      <div class="form-group">
        <label for="phone">Mobile Number</label>
        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="04xx xxx xxx">
      </div>

      <div class="form-group">
        <label for="address">Street Address</label>
        <input type="text" id="address" name="address" value="{{ old('address') }}" required>
      </div>

      <div class="form-group">
        <label for="business_name">Business Name <span class="optional">(optional)</span></label>
        <input type="text" id="business_name" name="business_name" value="{{ old('business_name') }}">
      </div>

      <div class="form-group">
        <label for="pin_code">Security PIN</label>
        <input type="password" id="pin_code" name="pin_code" maxlength="6" pattern="[0-9]{6}" required>
        <small class="form-hint">6-digit PIN used to verify you during remote support.</small>
      </div>

      <button type="submit" class="btn-primary w-full">Save & Finish Setup</button>
    </form>

  </div>
</section>
@endsection