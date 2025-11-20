@extends('admin.layouts.admin-layout')

@section('title', 'Add Component')

@section('content')
<div class="container py-4">
    <h2>Add Component</h2>

    <form action="{{ route('admin.components.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" rows="5" class="form-control"></textarea>
        </div>

        <button class="btn btn-success">Save</button>
        <a href="{{ route('admin.components.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
