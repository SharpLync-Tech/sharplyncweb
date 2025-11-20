@extends('admin.layouts.admin-layout')

@section('title', 'Edit Component')

@section('content')
<div class="container py-4">
    <h2>Edit Component</h2>

    <form action="{{ route('admin.components.update', $component->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ $component->name }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" rows="5" class="form-control">{{ $component->description }}</textarea>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="{{ route('admin.components.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
