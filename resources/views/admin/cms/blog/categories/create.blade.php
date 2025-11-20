@extends('admin.layouts.admin-layout')

@section('title', 'Create Blog Category')

@section('content')
<div class="container py-4">

    <h2>Create Blog Category</h2>

    <form action="{{ route('admin.cms.blog.categories.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Slug (leave empty to auto-generate)</label>
            <input type="text" name="slug" class="form-control">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input" checked>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Save</button>
        <a href="{{ route('admin.cms.blog.categories.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
