@extends('layouts.sharpfleet')

@section('title', 'Company Profile')

@section('sharpfleet-content')

<div style="max-width:700px;margin:40px auto;padding:0 16px;">

    <h1 style="margin-bottom:8px;">Company Profile</h1>

    <p style="margin-bottom:24px;color:#6b7280;">
        Define who your organisation is.
    </p>

    @if (session('success'))
        <div style="background:#dcfce7;color:#065f46;padding:12px 16px;border-radius:8px;margin-bottom:24px;">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ url('/app/sharpfleet/admin/company/profile') }}">
        @csrf

        <div style="background:white;padding:20px;border-radius:10px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.05);
                    margin-bottom:24px;">

            <label>Company name</label>
            <input type="text" name="name" value="{{ old('name', $organisation->name) }}" required style="width:100%;padding:10px;margin-bottom:12px;">

            <label>Company type</label>
            <select name="company_type" style="width:100%;padding:10px;margin-bottom:12px;">
                <option value="">— Select —</option>
                <option value="sole_trader" {{ $organisation->company_type === 'sole_trader' ? 'selected' : '' }}>Sole Trader</option>
                <option value="company" {{ $organisation->company_type === 'company' ? 'selected' : '' }}>Company</option>
            </select>

            <label>Industry</label>
            <input type="text" name="industry" value="{{ old('industry', $organisation->industry) }}" style="width:100%;padding:10px;margin-bottom:12px;">

            <label>Timezone</label>
            <select name="timezone" style="width:100%;padding:10px;">
                <option value="Australia/Brisbane" {{ $timezone === 'Australia/Brisbane' ? 'selected' : '' }}>Australia / Brisbane</option>
                <option value="Australia/Sydney" {{ $timezone === 'Australia/Sydney' ? 'selected' : '' }}>Australia / Sydney</option>
                <option value="Australia/Melbourne" {{ $timezone === 'Australia/Melbourne' ? 'selected' : '' }}>Australia / Melbourne</option>
            </select>
        </div>

        <div style="display:flex;gap:12px;">
            <button type="submit" style="background:#2CBFAE;color:white;padding:12px 20px;border-radius:6px;border:none;">
                Save
            </button>

            <button type="submit" name="save_and_return" value="1"
                    style="background:#e5e7eb;color:#111827;padding:12px 20px;border-radius:6px;border:none;">
                Save & return to Company
            </button>
        </div>

    </form>
</div>

@endsection
