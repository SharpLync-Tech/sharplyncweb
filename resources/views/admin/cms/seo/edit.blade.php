@extends('admin.layouts.admin-layout')

@section('title', 'Edit SEO Meta')

@section('content')
<div class="container py-4">

    <h2>Edit SEO Meta</h2>

    <form action="{{ route('admin.cms.seo.update', $seoMeta->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Page Slug</label>
            <input type="text" name="page_slug" class="form-control" value="{{ $seoMeta->page_slug }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Meta Title</label>
            <input type="text" name="meta_title" class="form-control" value="{{ $seoMeta->meta_title }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Meta Description</label>
            <textarea name="meta_description" rows="4" class="form-control">{{ $seoMeta->meta_description }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Meta Keywords</label>
            <textarea name="meta_keywords" rows="2" class="form-control">{{ $seoMeta->meta_keywords }}</textarea>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="is_active" class="form-check-input" {{ $seoMeta->is_active ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="{{ route('admin.cms.seo.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
