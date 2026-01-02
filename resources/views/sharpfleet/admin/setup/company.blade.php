@extends('layouts.sharpfleet')

@section('title', 'SharpFleet Setup')

@section('sharpfleet-content')

<div class="container">
    <div class="page-header">
        <h1 class="page-title">SharpFleet Setup</h1>
        <p class="page-description">
            Step 1 of 2 — Company details.
        </p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error mb-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card" style="max-width: 720px;">
        <div class="card-body">
            <div class="text-muted mb-3">
                These details are used across SharpFleet (emails, reports, and date/time display).
            </div>

            <form method="POST" action="/app/sharpfleet/admin/setup/company">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Company name</label>
                    <input type="text" name="company_name" class="form-control"
                           value="{{ old('company_name', $organisation->name ?? '') }}" required>
                    <div class="form-hint">Tip: use the trading name your drivers recognise.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Time zone</label>
                    <select name="timezone" class="form-control" required>
                        @php $selected = old('timezone', $settings['timezone'] ?? 'Australia/Brisbane'); @endphp
                        @foreach(($timezones ?? []) as $tz)
                            <option value="{{ $tz }}" {{ $selected === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                    </select>
                    <div class="form-hint">This controls how times are shown to drivers and admins.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Industry (optional)</label>
                    <input type="text" name="industry" class="form-control"
                           value="{{ old('industry', $settings['industry'] ?? '') }}"
                           placeholder="e.g. Trades, Facilities, Transport">
                    <div class="form-hint">Used for internal reference and onboarding only.</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Next</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
