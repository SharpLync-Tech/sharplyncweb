@extends('admin.layouts.admin-layout')

@section('title', 'Edit Service')

@section('content')
<div class="container py-4">

    <h2>Edit Service</h2>

    <form action="{{ route('admin.cms.services.update', $service->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="{{ $service->title }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Short Description</label>
            <textarea name="short_description" rows="2" class="form-control">{{ $service->short_description }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Long Description</label>
            <textarea name="long_description" rows="6" class="form-control">{{ $service->long_description }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Replace Icon (optional)</label>
            <input type="file" name="icon_path" class="form-control">
            @if($service->icon_path)
                <small>Current: {{ $service->icon_path }}</small>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label">Replace Image (optional)</label>
            <input type="file" name="image_path" class="form-control">
            @if($service->image_path)
                <small>Current: {{ $service->image_path }}</small>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="{{ $service->sort_order }}">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input"
                {{ $service->is_active ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="{{ route('admin.cms.services.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
