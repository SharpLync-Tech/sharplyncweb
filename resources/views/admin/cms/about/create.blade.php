@extends('admin.layouts.admin-layout')

@section('title', 'Add About Section')

@section('content')
<div class="container py-4">

    <h2>Add About Section</h2>

    <form action="{{ route('admin.cms.about.sections.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" rows="6" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input" checked>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Save</button>
        <a href="{{ route('admin.cms.about.sections.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
