@extends('admin.layouts.admin-layout')

@section('title', 'Edit Page')

@section('content')
<div class="container py-4">

    <h2>Edit Page</h2>

    <form action="{{ route('admin.cms.pages.update', $page->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" class="form-control" value="{{ $page->slug }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="{{ $page->title }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" rows="8" class="form-control">{{ $page->content }}</textarea>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input"
                {{ $page->is_active ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="{{ route('admin.cms.pages.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
