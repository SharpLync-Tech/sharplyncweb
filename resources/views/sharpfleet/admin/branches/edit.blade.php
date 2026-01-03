@extends('layouts.sharpfleet')

@section('title', 'Edit Branch')

@section('sharpfleet-content')

@php
    $isDefault = (int) ($branch->is_default ?? 0) === 1;
    $isActive = !property_exists($branch, 'is_active') ? true : ((int) ($branch->is_active ?? 1) === 1);
@endphp

<div class="max-w-700 mx-auto mt-4">
    <div class="page-header">
        <h1 class="page-title">Edit branch</h1>
        <p class="page-description">Update the branch name and timezone.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error mb-3">
            <strong>Please fix the errors below.</strong>
        </div>
    @endif

    <form method="POST" action="{{ url('/app/sharpfleet/admin/branches/' . (int) $branch->id) }}">
        @csrf

        <div class="card">
            <label class="form-label">Branch name</label>
            <input type="text" name="name" value="{{ old('name', (string) ($branch->name ?? '')) }}" required class="form-control">
            @error('name') <div class="text-error mb-2">{{ $message }}</div> @enderror

            <label class="form-label mt-2">Timezone (IANA)</label>
            <select name="timezone" required class="form-control">
                @php($selectedTimezone = (string) old('timezone', (string) ($branch->timezone ?? '')))
                @include('sharpfleet.partials.timezone-options', ['selectedTimezone' => $selectedTimezone])
            </select>
            <div class="form-hint">Use an IANA timezone for correct DST handling.</div>
            @error('timezone') <div class="text-error mb-2">{{ $message }}</div> @enderror

            <hr class="my-3">

            <input type="hidden" name="is_default" value="0">
            <label class="checkbox-label mb-2">
                <input type="checkbox" name="is_default" value="1" {{ old('is_default', $isDefault ? 1 : 0) == 1 ? 'checked' : '' }}>
                <strong>Default branch</strong>
            </label>

            <input type="hidden" name="is_active" value="0">
            <label class="checkbox-label mb-0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $isActive ? 1 : 0) == 1 ? 'checked' : '' }}>
                <strong>Active</strong>
            </label>

            <hr class="my-3">

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ url('/app/sharpfleet/admin/branches') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

@endsection
