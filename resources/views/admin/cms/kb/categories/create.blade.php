@extends('admin.layouts.admin-layout')

@section('title', 'Add KB Category')

@section('content')
<div class="container py-4">

    <h2>Add Knowledge Base Category</h2>

    <form action="{{ route('admin.cms.kb.categories.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Slug (auto-generated if empty)</label>
            <input type="text" name="slug" class="form-control">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input" checked>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Save</button>
        <a href="{{ route('admin.cms.kb.categories.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
