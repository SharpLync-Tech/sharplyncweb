@extends('admin.layouts.admin-layout')

@section('title', 'Add Timeline Item')

@section('content')
<div class="container py-4">

    <h2>Add Timeline Item</h2>

    <form action="{{ route('admin.cms.about.timeline.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Year</label>
            <input type="text" name="year" class="form-control" placeholder="2024" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" rows="5" class="form-control"></textarea>
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
        <a href="{{ route('admin.cms.about.timeline.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
