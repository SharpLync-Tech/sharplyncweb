@extends('admin.layouts.admin-layout')

@section('title', 'Add Value')

@section('content')
<div class="container py-4">

    <h2>Add Value</h2>

    <form action="{{ route('admin.cms.about.values.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" rows="6" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Icon (optional)</label>
            <input type="file" name="icon_path" class="form-control">
            <small class="text-muted">Accepted: PNG, JPG, SVG, WEBP</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input" checked>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Save</button>
        <a href="{{ route('admin.cms.about.values.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
