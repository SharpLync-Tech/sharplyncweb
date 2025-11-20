@extends('admin.layouts.admin-layout')

@section('title', 'Create Page')

@section('content')
<div class="container py-4">

    <h2>Create Page</h2>

    <form action="{{ route('admin.cms.pages.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" class="form-control" placeholder="about, contact, privacy-policy" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" rows="8" class="form-control"></textarea>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input" checked>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Save</button>
        <a href="{{ route('admin.cms.pages.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
