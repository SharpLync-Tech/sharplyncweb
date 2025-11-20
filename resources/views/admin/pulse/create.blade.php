@extends('admin.layouts.admin-layout')

@section('title', 'Add Pulse Item')

@section('content')
<div class="container py-4">
    <h2>Add Pulse Feed Item</h2>

    <form action="{{ route('admin.pulse.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea name="message" rows="5" class="form-control" required></textarea>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input" checked>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success">Save</button>
        <a href="{{ route('admin.pulse.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
