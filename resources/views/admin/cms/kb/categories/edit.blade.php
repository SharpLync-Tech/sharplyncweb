@extends('admin.layouts.admin-layout')

@section('title', 'Edit KB Category')

@section('content')
<div class="container py-4">

    <h2>Edit Knowledge Base Category</h2>

    <form action="{{ route('admin.cms.kb.categories.update', $category->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" class="form-control" value="{{ $category->slug }}">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input" 
                {{ $category->is_active ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="{{ route('admin.cms.kb.categories.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
