@extends('admin.layouts.admin-layout')

@section('title', 'Edit SharpFleet Subscriber')

@section('content')
<div class="container-fluid">
    <div class="sl-page-header d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h2 class="fw-semibold">Edit Subscriber</h2>
            <div class="sl-subtitle small">{{ $organisation->name ?? 'Organisation' }} (ID: {{ $organisation->id }})</div>
            <div class="text-muted small">All times shown in AEST (Brisbane time).</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('admin.sharpfleet.organisations.show', $organisation->id) }}">Back</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            Please check the form and try again.
        </div>
    @endif

    <div class="card sl-card">
        <div class="card-header py-3">
            <div class="fw-semibold">Subscriber Details</div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.sharpfleet.organisations.update', $organisation->id) }}" class="row g-3">
                @csrf
                @method('PATCH')

                <div class="col-12 col-lg-6">
                    <label class="form-label">Organisation Name</label>
                    <input type="text" name="name" value="{{ old('name', $organisation->name) }}" class="form-control" required>
                    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-lg-6">
                    <label class="form-label">Industry</label>
                    <input type="text" name="industry" value="{{ old('industry', $organisation->industry) }}" class="form-control">
                    @error('industry')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-lg-6">
                    <label class="form-label">Company Type</label>
                    <input type="text" name="company_type" value="{{ old('company_type', $organisation->company_type) }}" class="form-control" placeholder="e.g. company / sole_trader">
                    @error('company_type')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-lg-6">
                    <label class="form-label">Subscriber Timezone</label>
                    <input type="text" class="form-control" value="{{ $timezone ?? 'Australia/Brisbane' }}" disabled>
                    <div class="text-muted small mt-1">Timezone is managed inside the subscriber’s SharpFleet tenant settings.</div>
                </div>

                <div class="col-12">
                    <hr>
                    <div class="fw-semibold mb-2">Trial Override / Extension</div>
                    <div class="text-muted small mb-3">Set an explicit trial end, or extend the existing trial by N days.</div>
                </div>

                <div class="col-12 col-lg-6">
                    <label class="form-label">Trial Ends (Brisbane time)</label>
                    <input type="datetime-local" name="trial_ends_at" value="{{ old('trial_ends_at', $trialEndsBrisbane) }}" class="form-control">
                    <div class="text-muted small mt-1">Leave blank to clear the organisation-level trial end.</div>
                    @error('trial_ends_at')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-lg-6">
                    <label class="form-label">Extend Trial (days)</label>
                    <input type="number" name="extend_trial_days" value="{{ old('extend_trial_days') }}" class="form-control" min="1" max="3650" placeholder="e.g. 30">
                    <div class="text-muted small mt-1">Extends from current trial end (if in the future), otherwise from now.</div>
                    @error('extend_trial_days')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Save Changes</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.sharpfleet.organisations.show', $organisation->id) }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
