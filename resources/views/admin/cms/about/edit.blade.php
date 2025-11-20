@extends('admin.layouts.admin-layout')

@section('title', 'Edit About Section')

@section('content')
<div class="container py-4">

    <h2>Edit About Section</h2>

    <form action="{{ route('admin.cms.about.sections.update', $section->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="{{ $section->title }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" rows="6" class="form-control">{{ $section->content }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="{{ $section->sort_order }}">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input"
                {{ $section->is_active ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="{{ route('admin.cms.about.sections.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
