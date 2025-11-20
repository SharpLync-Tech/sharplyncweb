@extends('admin.layouts.admin-layout')

@section('title', 'Edit Pulse Item')

@section('content')
<div class="container py-4">
    <h2>Edit Pulse Feed Item</h2>

    <form action="{{ route('admin.pulse.update', $pulse->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="{{ $pulse->title }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea name="message" rows="5" class="form-control" required>
                {{ $pulse->message }}
            </textarea>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input"
                   {{ $pulse->is_active ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="{{ route('admin.pulse.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
