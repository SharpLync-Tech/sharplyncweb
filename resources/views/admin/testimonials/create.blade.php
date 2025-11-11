@extends('admin.layouts.admin-layout')

@section('title', 'Add Testimonial')

@section('content')
  <h2>Add New Testimonial</h2>
  <p class="mt-1">Enter customer feedback below and save to the SharpLync CMS.</p>

  <div class="admin-card mt-2" style="max-width:900px;">
    <form action="{{ route('admin.testimonials.store') }}" method="POST">
      @csrf

      <div class="mt-2">
        <label style="font-weight:700;">Customer Name *</label>
        <input name="customer_name" type="text" value="{{ old('customer_name') }}" class="form-control" required>
        @error('customer_name') <div class="form-help" style="color:#C62828">{{ $message }}</div> @enderror
      </div>

      <div class="form-row mt-2">
        <div class="form-col">
          <label style="font-weight:700;">Position (optional)</label>
          <input name="customer_position" type="text" value="{{ old('customer_position') }}" class="form-control">
        </div>
        <div class="form-col">
          <label style="font-weight:700;">Company (optional)</label>
          <input name="customer_company" type="text" value="{{ old('customer_company') }}" class="form-control">
        </div>
      </div>

      <div class="mt-2">
        <label style="font-weight:700;">Testimonial *</label>
        <textarea name="testimonial_text" rows="7" class="form-control" required>{{ old('testimonial_text') }}</textarea>
      </div>

      <div class="form-row mt-2">
        <div class="form-col">
          <label style="font-weight:700;">Rating (1–5)</label>
          <input name="rating" type="number" min="1" max="5" value="{{ old('rating') }}" class="form-control">
        </div>
        <div class="form-col">
          <label style="font-weight:700;">Display Order (0+)</label>
          <input name="display_order" type="number" min="0" value="{{ old('display_order', 0) }}" class="form-control">
        </div>
      </div>

      <div class="mt-2" style="display:flex;gap:24px;align-items:center;flex-wrap:wrap;">
        <label style="display:flex;align-items:center;gap:8px;">
          <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
          Featured
        </label>
        <label style="display:flex;align-items:center;gap:8px;">
          <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
          Active
        </label>
      </div>

      <div class="mt-3" style="display:flex;gap:12px;">
        <button type="submit" class="btn btn-accent">Save Testimonial</button>
        <a href="{{ route('admin.testimonials.index') }}" class="btn btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
@endsection