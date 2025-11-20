@extends('admin.layouts.admin-layout')

@section('title', 'Edit Value')

@section('content')
<div class="container py-4">

    <h2>Edit Value</h2>

    <form action="{{ route('admin.cms.about.values.update', $value->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="{{ $value->title }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" rows="6" class="form-control">{{ $value->content }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Replace Icon (optional)</label>
            <input type="file" name="icon_path" class="form-control">
            @if($value->icon_path)
                <br>
                <img src="{{ asset('storage/'.$value->icon_path) }}" width="60" alt="">
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="{{ $value->sort_order }}">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input"
                {{ $value->is_active ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="{{ route('admin.cms.about.values.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
