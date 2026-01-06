@extends('layouts.sharpfleet')

@section('title', 'Company Profile')

@section('sharpfleet-content')

<div class="max-w-700 mx-auto mt-4">

    <h1 class="page-title mb-1">Company Profile</h1>

    <p class="page-description mb-3">
        Define who your organisation is.
    </p>

    @if (session('success'))
        <div class="alert alert-success mb-3">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ url('/app/sharpfleet/admin/company/profile') }}" class="card">
        @csrf

        <div class="mb-3">

            <label class="form-label">Company name</label>
            <input type="text" name="name" value="{{ old('name', $organisation->name) }}" required class="form-control mb-2">

            <label class="form-label">Industry</label>
            <input type="text" name="industry" value="{{ old('industry', $organisation->industry) }}" class="form-control mb-2">

            <label class="form-label">Timezone</label>
            <select name="timezone" class="form-control">
                @php($selectedTimezone = (string) old('timezone', (string) ($timezone ?? 'Australia/Brisbane')))
                @include('sharpfleet.partials.timezone-options', ['selectedTimezone' => $selectedTimezone])
            </select>
        </div>

        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-primary">
                Save
            </button>
            <button type="submit" name="save_and_return" value="1" class="btn btn-secondary">
                Save & return to Company
            </button>
        </div>

    </form>
</div>

@endsection
