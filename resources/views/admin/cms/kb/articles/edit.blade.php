@extends('admin.layouts.admin-layout')

@section('title', 'Edit KB Article')

@section('content')
<div class="container py-4">

    <h2>Edit Knowledge Base Article</h2>

    <form action="{{ route('admin.cms.kb.articles.update', $article->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Article Title</label>
            <input type="text" name="title" class="form-control" value="{{ $article->title }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" class="form-control" value="{{ $article->slug }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $cat->id == $article->category_id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Summary</label>
            <textarea name="summary" rows="3" class="form-control">{{ $article->summary }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" rows="8" class="form-control">{{ $article->content }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Replace Attachment (optional)</label>
            <input type="file" name="attachment" class="form-control">
            @if($article->attachment)
                <br>
                <a href="{{ asset('storage/'.$article->attachment) }}" target="_blank">
                    Current file
                </a>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label">Publish Date</label>
            <input type="date" name="published_at" class="form-control"
                   value="{{ $article->published_at ? $article->published_at->format('Y-m-d') : '' }}">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input"
                {{ $article->is_active ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="{{ route('admin.cms.kb.articles.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
