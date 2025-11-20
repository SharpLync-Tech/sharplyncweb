@extends('admin.layouts.admin-layout')

@section('title', 'Create SEO Meta')

@section('content')
<div class="container py-4">

    <h2>Create SEO Meta Entry</h2>

    <form action="{{ route('admin.cms.seo.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Page Slug</label>
            <input type="text" name="page_slug" class="form-control" placeholder="about, services, contact" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Meta Title</label>
            <input type="text" name="meta_title" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Meta Description</label>
            <textarea name="meta_description" rows="4" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Meta Keywords</label>
            <textarea name="meta_keywords" rows="2" class="form-control"></textarea>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="is_active" class="form-check-input" checked>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Save</button>
        <a href="{{ route('admin.cms.seo.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
