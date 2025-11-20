@extends('admin.layouts.admin-layout')

@section('title', 'Create Footer Link')

@section('content')
<div class="container py-4">

    <h2>Create Footer Link</h2>

    <form action="{{ route('admin.cms.footer.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Label</label>
            <input type="text" name="label" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">URL</label>
            <input type="text" name="url" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Column (1–4)</label>
            <input type="number" name="column" class="form-control" min="1" max="4" required>
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
        <a href="{{ route('admin.cms.footer.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
