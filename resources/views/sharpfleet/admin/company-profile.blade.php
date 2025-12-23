@extends('layouts.sharpfleet')

@section('title', 'Company Profile')

@section('sharpfleet-content')

<div style="max-width:700px;margin:40px auto;padding:0 16px;">

    <h1 style="margin-bottom:8px;">Company Profile</h1>

    <p style="margin-bottom:24px;color:#6b7280;">
        Define who your organisation is. These details are used across SharpFleet.
    </p>

    {{-- Success message --}}
    @if (session('success'))
        <div style="background:#dcfce7;color:#065f46;
                    padding:12px 16px;border-radius:8px;
                    margin-bottom:24px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Validation errors --}}
    @if ($errors->any())
        <div style="background:#fee2e2;color:#7f1d1d;
                    padding:12px 16px;border-radius:8px;
                    margin-bottom:24px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ url('/app/sharpfleet/admin/company/profile') }}">
        @csrf

        <div style="background:white;padding:20px;border-radius:10px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.05);
                    margin-bottom:24px;">

            <div style="margin-bottom:16px;">
                <label style="display:block;font-weight:600;margin-bottom:6px;">
                    Company name
                </label>
                <input type="text"
                       name="name"
                       value="{{ old('name', $organisation->name) }}"
                       required
                       style="width:100%;padding:10px;">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-weight:600;margin-bottom:6px;">
                    Company type
                </label>
                <select name="company_type" style="width:100%;padding:10px;">
                    <option value="">— Select —</option>
                    <option value="sole_trader"
                        {{ old('company_type', $organisation->company_type) === 'sole_trader' ? 'selected' : '' }}>
                        Sole Trader
                    </option>
                    <option value="company"
                        {{ old('company_type', $organisation->company_type) === 'company' ? 'selected' : '' }}>
                        Company
                    </option>
                </select>
            </div>

            <div>
                <label style="display:block;font-weight:600;margin-bottom:6px;">
                    Industry
                </label>
                <input type="text"
                       name="industry"
                       value="{{ old('industry', $organisation->industry) }}"
                       placeholder="e.g. Transport, Construction, IT Services"
                       style="width:100%;padding:10px;">
            </div>

        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit"
                    name="save"
                    value="1"
                    style="background:#2CBFAE;color:white;
                           border:none;padding:12px 20px;
                           border-radius:6px;font-weight:600;">
                Save
            </button>

            <button type="submit"
                    name="save_and_return"
                    value="1"
                    style="background:#e5e7eb;color:#111827;
                           border:none;padding:12px 20px;
                           border-radius:6px;font-weight:600;">
                Save & return to Company
            </button>
        </div>

    </form>
</div>

@endsection
