@extends('admin.layouts.admin-layout')

@section('title', 'Edit Timeline Item')

@section('content')
<div class="container py-4">

    <h2>Edit Timeline Item</h2>

    <form action="{{ route('admin.cms.about.timeline.update', $timelineItem->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Year</label>
            <input type="text" name="year" class="form-control" value="{{ $timelineItem->year }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="{{ $timelineItem->title }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" rows="5" class="form-control">{{ $timelineItem->description }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="{{ $timelineItem->sort_order }}">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input"
                {{ $timelineItem->is_active ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="{{ route('admin.cms.about.timeline.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
