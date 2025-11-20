@extends('admin.layouts.admin-layout')

@section('title', 'Create Blog Post')

@section('content')
<div class="container py-4">

    <h2>Create Blog Post</h2>

    <form action="{{ route('admin.cms.blog.posts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Post Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Slug (leave empty to auto-generate)</label>
            <input type="text" name="slug" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Choose Category --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Summary</label>
            <textarea name="summary" rows="3" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" rows="8" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Featured Image</label>
            <input type="file" name="featured_image" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Publish Date</label>
            <input type="date" name="published_at" class="form-control">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input" checked>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Save</button>
        <a href="{{ route('admin.cms.blog.posts.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
