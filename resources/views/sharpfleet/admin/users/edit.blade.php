@extends('layouts.sharpfleet')

@section('title', 'Edit User')

@section('sharpfleet-content')

<div class="max-w-700 mx-auto mt-4">
    <div class="page-header">
        <h1 class="page-title">Edit User</h1>
        <p class="page-description">Toggle whether this user can access the Driver View.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error mb-3">
            <strong>Please fix the errors below.</strong>
        </div>
    @endif

    <div class="card">
        <div class="mb-3">
            <div class="info-item">
                <div class="info-label">Name</div>
                <div class="info-value">{{ trim($user->first_name.' '.$user->last_name) }}</div>
            </div>
        </div>

        <div class="mb-3">
            <div class="info-item">
                <div class="info-label">Email</div>
                <div class="info-value">{{ $user->email }}</div>
            </div>
        </div>

        <div class="mb-3">
            <div class="info-item">
                <div class="info-label">Role</div>
                <div class="info-value">{{ $user->role }}</div>
            </div>
        </div>

        <form method="POST" action="{{ url('/app/sharpfleet/admin/users/'.$user->id) }}">
            @csrf

            <input type="hidden" name="is_driver" value="0">

            <label class="checkbox-label mb-2">
                <input type="checkbox" name="is_driver" value="1" {{ (int)($user->is_driver ?? 0) === 1 ? 'checked' : '' }}>
                <strong>Enable Driver View access</strong>
            </label>

            <p class="text-muted small mb-3">
                If enabled, this user can open /app/sharpfleet/driver. This is useful when an admin also drives.
            </p>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ url('/app/sharpfleet/admin/users') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
