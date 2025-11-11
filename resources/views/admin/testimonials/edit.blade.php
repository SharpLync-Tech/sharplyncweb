@extends('admin.layouts.admin-layout')

@section('title', 'Edit Testimonial')

@section('content')
  <h2>Edit Testimonial</h2>
  <p class="mt-1">Update customer feedback and save.</p>

  <div class="admin-card mt-2" style="max-width:900px;">
    <form action="{{ route('admin.testimonials.update', $t->id) }}" method="POST">
      @csrf @method('PUT')

      <div class="mt-2">
        <label style="font-weight:700;">Customer Name *</label>
        <input name="customer_name" type="text" value="{{ old('customer_name', $t->customer_name) }}" class="form-control" required>
      </div>

      <div class="form-row mt-2">
        <div class="form-col">
          <label style="font-weight:700;">Position (optional)</label>
          <input name="customer_position" type="text" value="{{ old('customer_position', $t->customer_position) }}" class="form-control">
        </div>
        <div class="form-col">
          <label style="font-weight:700;">Company (optional)</label>
          <input name="customer_company" type="text" value="{{ old('customer_company', $t->customer_company) }}" class="form-control">
        </div>
      </div>

      <div class="mt-2">
        <label style="font-weight:700;">Testimonial *</label>
        <textarea name="testimonial_text" rows="7" class="form-control" required>{{ old('testimonial_text', $t->testimonial_text) }}</textarea>
      </div>

      <div class="form-row mt-2">
        <div class="form-col">
          <label style="font-weight:700;">Rating (1–5)</label>
          <input name="rating" type="number" min="1" max="5" value="{{ old('rating', $t->rating) }}" class="form-control">
        </div>
        <div class="form-col">
          <label style="font-weight:700;">Display Order (0+)</label>
          <input name="display_order" type="number" min="0" value="{{ old('display_order', $t->display_order) }}" class="form-control">
        </div>
      </div>

      <div class="mt-2" style="display:flex;gap:24px;align-items:center;flex-wrap:wrap;">
        <label style="display:flex;align-items:center;gap:8px;">
          <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $t->is_featured) ? 'checked' : '' }}>
          Featured
        </label>
        <label style="display:flex;align-items:center;gap:8px;">
          <input type="checkbox" name="is_active" value="1" {{ old('is_active', $t->is_active) ? 'checked' : '' }}>
          Active
        </label>
      </div>

      <div class="mt-3" style="display:flex;gap:12px;">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('admin.testimonials.index') }}" class="btn btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
@endsection