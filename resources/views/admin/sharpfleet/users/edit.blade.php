@extends('admin.layouts.admin-layout')

@section('title', 'Edit SharpFleet User')

@section('content')
<div class="container-fluid">
    <div class="sl-page-header d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h2 class="fw-semibold">Edit User</h2>
            <div class="sl-subtitle small">
                {{ $organisation->name ?? 'Organisation' }} (ID: {{ $organisation->id }})
            </div>
            <div class="text-muted small">All times shown in AEST (Brisbane time).</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('admin.sharpfleet.organisations.users', $organisation->id) }}">Back to users</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            Please check the form and try again.
        </div>
    @endif

    <div class="card sl-card">
        <div class="card-header py-3">
            <div class="fw-semibold">User Details</div>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-12 col-lg-6">
                    <div class="text-muted small">Name</div>
                    <div class="fw-semibold">{{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: '—' }}</div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="text-muted small">Email</div>
                    <div class="fw-semibold">{{ $user->email ?? '—' }}</div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="text-muted small">Role</div>
                    <div><span class="badge text-bg-light border">{{ $user->role ?? '—' }}</span></div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.sharpfleet.organisations.users.update', [$organisation->id, $user->id]) }}" class="row g-3">
                @csrf
                @method('PATCH')

                <div class="col-12">
                    <div class="fw-semibold mb-2">Trial Override (User-level)</div>
                    <div class="text-muted small mb-3">If set, this overrides the organisation-level trial end for this user.</div>
                </div>

                <div class="col-12 col-lg-6">
                    <label class="form-label">Trial Ends (Brisbane time)</label>
                    <input type="datetime-local" name="trial_ends_at" value="{{ old('trial_ends_at', $trialEndsBrisbane) }}" class="form-control">
                    <div class="text-muted small mt-1">Leave blank to clear user-level override.</div>
                    @error('trial_ends_at')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-lg-6">
                    <label class="form-label">Extend Trial (days)</label>
                    <input type="number" name="extend_trial_days" value="{{ old('extend_trial_days') }}" class="form-control" min="1" max="3650" placeholder="e.g. 14">
                    <div class="text-muted small mt-1">Extends from current user trial end (if future), otherwise from now.</div>
                    @error('extend_trial_days')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Save Changes</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.sharpfleet.organisations.users', $organisation->id) }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
