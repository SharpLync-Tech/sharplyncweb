@extends('admin.layouts.admin-layout')

@section('title', 'Edit Testimonial')

@section('content')

    <div class="admin-top-bar" style="margin-bottom: 12px;">
        <h2>Edit Testimonial</h2>
    </div>

    <p style="margin-top:-6px;color:#4b5a6a;">Update customer feedback and save.</p>

    <div class="admin-card" style="max-width:900px;">

        <form action="{{ route('admin.testimonials.update', $t->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- CUSTOMER NAME --}}
            <div class="form-group">
                <label class="form-label">Customer Name *</label>
                <input name="customer_name"
                       type="text"
                       class="form-input"
                       required
                       value="{{ old('customer_name', $t->customer_name) }}">
            </div>

            {{-- POSITION + COMPANY --}}
            <div class="form-row">
                <div class="form-col">
                    <label class="form-label">Position (optional)</label>
                    <input name="customer_position"
                           type="text"
                           class="form-input"
                           value="{{ old('customer_position', $t->customer_position) }}">
                </div>

                <div class="form-col">
                    <label class="form-label">Company (optional)</label>
                    <input name="customer_company"
                           type="text"
                           class="form-input"
                           value="{{ old('customer_company', $t->customer_company) }}">
                </div>
            </div>

            {{-- TESTIMONIAL --}}
            <div class="form-group">
                <label class="form-label">Testimonial *</label>
                <textarea name="testimonial_text"
                          rows="7"
                          class="form-input"
                          required>{{ old('testimonial_text', $t->testimonial_text) }}</textarea>
            </div>

            {{-- RATING + ORDER --}}
            <div class="form-row">
                <div class="form-col">
                    <label class="form-label">Rating (1–5)</label>
                    <input name="rating"
                           type="number"
                           min="1"
                           max="5"
                           class="form-input"
                           value="{{ old('rating', $t->rating) }}">
                </div>

                <div class="form-col">
                    <label class="form-label">Display Order (0+)</label>
                    <input name="display_order"
                           type="number"
                           min="0"
                           class="form-input"
                           value="{{ old('display_order', $t->display_order) }}">
                </div>
            </div>

            {{-- CHECKBOXES --}}
            <div class="form-checkbox-row">
                <label class="form-checkbox">
                    <input type="checkbox"
                           name="is_featured"
                           value="1"
                           {{ old('is_featured', $t->is_featured) ? 'checked' : '' }}>
                    <span>Featured</span>
                </label>

                <label class="form-checkbox">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', $t->is_active) ? 'checked' : '' }}>
                    <span>Active</span>
                </label>
            </div>

            {{-- BUTTONS --}}
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.testimonials.index') }}" class="btn btn-ghost">Cancel</a>
            </div>

        </form>
    </div>

@endsection
