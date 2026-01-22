@extends('layouts.sharpfleet')

@section('title', 'Add Branch')

@section('sharpfleet-content')

@php
    $defaultTimezone = (string) ($defaultTimezone ?? '');
    $companyDistanceUnit = (string) ($companyDistanceUnit ?? 'km');
    $selectedDistanceUnit = (string) old('distance_unit', '');
@endphp

<div class="max-w-700 mx-auto mt-4">
    <div class="page-header">
        <h1 class="page-title">Add branch</h1>
        <p class="page-description">Branches control timezone display and booking/trip time interpretation.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error mb-3">
            <strong>Please fix the errors below.</strong>
        </div>
    @endif

    <form method="POST" action="{{ url('/app/sharpfleet/admin/branches') }}">
        @csrf

        <div class="card">
            <label class="form-label">Branch name</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="form-control" placeholder="e.g. Brisbane Depot">
            @error('name') <div class="text-error mb-2">{{ $message }}</div> @enderror

            <label class="form-label mt-2">Timezone (IANA)</label>
            <select name="timezone" required class="form-control">
                @php($selectedTimezone = (string) old('timezone', $defaultTimezone))
                @include('sharpfleet.partials.timezone-options', ['selectedTimezone' => $selectedTimezone])
            </select>
            <div class="form-hint">Use an IANA timezone for correct DST handling (e.g. Europe/London).</div>
            @error('timezone') <div class="text-error mb-2">{{ $message }}</div> @enderror

            <input type="hidden" name="is_default" value="0">
            <label class="checkbox-label mt-2 mb-0">
                <input type="checkbox" name="is_default" value="1" {{ old('is_default', 0) == 1 ? 'checked' : '' }}>
                <strong>Make this the default branch</strong>
            </label>

            <label class="form-label mt-3">Distance unit (optional override)</label>
            <select name="distance_unit" class="form-control">
                <option value="" {{ $selectedDistanceUnit === '' ? 'selected' : '' }}>Inherit company default ({{ $companyDistanceUnit }})</option>
                <option value="km" {{ $selectedDistanceUnit === 'km' ? 'selected' : '' }}>Kilometres (km)</option>
                <option value="mi" {{ $selectedDistanceUnit === 'mi' ? 'selected' : '' }}>Miles (mi)</option>
            </select>
            <div class="form-hint">Use this if different branches operate in different distance units.</div>
            @error('distance_unit') <div class="text-error mb-2">{{ $message }}</div> @enderror
        </div>

        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ url('/app/sharpfleet/admin/branches') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection
