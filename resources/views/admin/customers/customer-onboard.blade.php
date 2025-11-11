@extends('layouts.content')

@section('title', 'Customer Onboarding | SharpLync')

@section('content')
<section class="content-hero fade-section">
  <div class="content-header">
    <h1>Welcome to <span style="color:#2CBFAE;">SharpLync</span></h1>
    <p>Let’s get you set up for secure support and billing. Your information helps us provide better, faster service when you need IT most.</p>
  </div>

  <div class="content-card">
    {{-- ✅ Success Message --}}
    @if(session('success'))
      <div class="alert success">{{ session('success') }}</div>
    @endif

    {{-- ❌ Error Message --}}
    @if(session('error'))
      <div class="alert error">{{ session('error') }}</div>
    @endif

    {{-- Laravel Validation Errors --}}
    @if ($errors->any())
      <div class="alert error">
        <strong>Please fix the following errors:</strong>
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- ✅ Onboarding Form --}}
    <form method="POST" action="{{ route('customers.store') }}">
      @csrf

      <div class="form-grid">
        <div>
          <label>First Name *</label>
          <input type="text" name="first_name" value="{{ old('first_name') }}" required>
        </div>
        <div>
          <label>Last Name *</label>
          <input type="text" name="last_name" value="{{ old('last_name') }}" required>
        </div>
      </div>

      <div class="form-group">
        <label>Company (optional)</label>
        <input type="text" name="company_name" value="{{ old('company_name') }}">
      </div>

      <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" value="{{ old('email') }}" required>
      </div>

      <div class="form-group">
        <label>Phone</label>
        <input type="text" name="phone" value="{{ old('phone') }}">
      </div>

      <div class="form-group">
        <label>Address</label>
        <input type="text" name="address" value="{{ old('address') }}">
      </div>

      <div class="form-grid">
        <div>
          <label>City</label>
          <input type="text" name="city" value="{{ old('city') }}">
        </div>
        <div>
          <label>State</label>
          <input type="text" name="state" value="{{ old('state') }}">
        </div>
        <div>
          <label>Postcode</label>
          <input type="text" name="postcode" value="{{ old('postcode') }}">
        </div>
      </div>

      <button type="submit" class="btn-primary">Create Account</button>
    </form>
  </div>
</section>

@push('styles')
<style>
  /* Scoped overrides for this form only */
  .content-card form { width:100%; }
  label { font-weight:600; color:#0A2A4D; display:block; margin-bottom:6px; }
  input {
    width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:15px;
    font-family:'Poppins',sans-serif; margin-bottom:15px;
  }
  input:focus { outline:none; border-color:#2CBFAE; box-shadow:0 0 4px #2CBFAE; }
  .form-grid { display:flex; gap:15px; flex-wrap:wrap; }
  .form-grid > div { flex:1; min-width:150px; }
  .btn-primary {
    background:#104946; color:white; border:none; padding:12px 20px;
    border-radius:10px; font-size:16px; font-weight:600; cursor:pointer;
    transition:background 0.2s ease;
  }
  .btn-primary:hover { background:#0A2A4D; }
  .alert {
    border-radius:8px; padding:12px 16px; margin-bottom:15px; font-size:0.95rem;
  }
  .alert.success { background:#d4edda; color:#155724; }
  .alert.error { background:#f8d7da; color:#721c24; }
</style>
@endpush
@endsection