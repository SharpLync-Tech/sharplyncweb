@extends('admin.layouts.admin-layout')

@section('title', 'Create KB Article')

@section('content')
<div class="container py-4">

    <h2>Create Knowledge Base Article</h2>

    <form action="{{ route('admin.cms.kb.articles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Article Title</label>
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
            <label class="form-label">Attachment (optional)</label>
            <input type="file" name="attachment" class="form-control">
            <small class="text-muted">PDF, DOCX, TXT, images etc — max 8MB</small>
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
        <a href="{{ route('admin.cms.kb.articles.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
