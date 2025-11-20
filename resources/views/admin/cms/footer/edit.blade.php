@extends('admin.layouts.admin-layout')

@section('title', 'Edit Footer Link')

@section('content')
<div class="container py-4">

    <h2>Edit Footer Link</h2>

    <form action="{{ route('admin.cms.footer.update', $footerLink->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Label</label>
            <input type="text" name="label" class="form-control" value="{{ $footerLink->label }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">URL</label>
            <input type="text" name="url" class="form-control" value="{{ $footerLink->url }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Column (1–4)</label>
            <input type="number" name="column" class="form-control" min="1" max="4" value="{{ $footerLink->column }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="{{ $footerLink->sort_order }}">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input"
                {{ $footerLink->is_active ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="{{ route('admin.cms.footer.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
