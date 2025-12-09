@extends('admin.layouts.admin-layout')

@section('title', 'Add Testimonial')

@section('content')

    <div class="admin-top-bar" style="margin-bottom: 12px;">
        <h2>Add New Testimonial</h2>
    </div>

    <p style="margin-top:-6px;color:#4b5a6a;">
        Enter customer feedback below and save to the SharpLync CMS.
    </p>

    <div class="admin-card" style="max-width:900px;">

        <form action="{{ route('admin.testimonials.store') }}" method="POST">
            @csrf

            {{-- CUSTOMER NAME --}}
            <div class="form-group">
                <label class="form-label">Customer Name *</label>
                <input 
                    name="customer_name"
                    type="text"
                    class="form-input"
                    value="{{ old('customer_name') }}"
                    required
                >
                @error('customer_name')
                    <div class="form-help" style="color:#C62828">{{ $message }}</div>
                @enderror
            </div>

            {{-- POSITION + COMPANY --}}
            <div class="form-row">
                <div class="form-col">
                    <label class="form-label">Position (optional)</label>
                    <input 
                        name="customer_position"
                        type="text"
                        class="form-input"
                        value="{{ old('customer_position') }}"
                    >
                </div>

                <div class="form-col">
                    <label class="form-label">Company (optional)</label>
                    <input 
                        name="customer_company"
                        type="text"
                        class="form-input"
                        value="{{ old('customer_company') }}"
                    >
                </div>
            </div>

            {{-- TESTIMONIAL --}}
            <div class="form-group">
                <label class="form-label">Testimonial *</label>
                <textarea 
                    name="testimonial_text"
                    rows="7"
                    class="form-input"
                    required
                >{{ old('testimonial_text') }}</textarea>
            </div>

            {{-- RATING + ORDER --}}
            <div class="form-row">
                <div class="form-col">
                    <label class="form-label">Rating (1–5)</label>
                    <input 
                        name="rating"
                        type="number"
                        min="1"
                        max="5"
                        class="form-input"
                        value="{{ old('rating') }}"
                    >
                </div>

                <div class="form-col">
                    <label class="form-label">Display Order (0+)</label>
                    <input 
                        name="display_order"
                        type="number"
                        min="0"
                        class="form-input"
                        value="{{ old('display_order', 0) }}"
                    >
                </div>
            </div>

            {{-- CHECKBOXES --}}
            <div class="form-checkbox-row">
                <label class="form-checkbox">
                    <input 
                        type="checkbox" 
                        name="is_featured" 
                        value="1" 
                        {{ old('is_featured') ? 'checked' : '' }}
                    >
                    <span>Featured</span>
                </label>

                <label class="form-checkbox">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1" 
                        {{ old('is_active', 1) ? 'checked' : '' }}
                    >
                    <span>Active</span>
                </label>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="form-actions">
                <button type="submit" class="btn btn-accent">Save Testimonial</button>
                <a href="{{ route('admin.testimonials.index') }}" class="btn btn-ghost">Cancel</a>
            </div>

        </form>

    </div>

@endsection
