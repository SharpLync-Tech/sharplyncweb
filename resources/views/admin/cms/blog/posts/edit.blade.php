@extends('admin.layouts.admin-layout')

@section('title', 'Edit Blog Post')

@section('content')
<div class="container py-4">

    <h2>Edit Blog Post</h2>

    <form action="{{ route('admin.cms.blog.posts.update', $post->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Post Title</label>
            <input type="text" name="title" class="form-control" value="{{ $post->title }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" class="form-control" value="{{ $post->slug }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" 
                        {{ $cat->id == $post->category_id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Summary</label>
            <textarea name="summary" rows="3" class="form-control">{{ $post->summary }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" rows="8" class="form-control">{{ $post->content }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Replace Featured Image (optional)</label>
            <input type="file" name="featured_image" class="form-control">
            @if($post->featured_image)
                <br>
                <img src="{{ asset('storage/' . $post->featured_image) }}" width="120" style="border-radius:4px;">
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label">Publish Date</label>
            <input type="date" name="published_at" class="form-control"
                   value="{{ $post->published_at ? $post->published_at->format('Y-m-d') : '' }}">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input"
                   {{ $post->is_active ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="{{ route('admin.cms.blog.posts.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
