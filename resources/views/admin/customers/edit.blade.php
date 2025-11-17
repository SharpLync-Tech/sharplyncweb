@extends('admin.layouts.admin-layout')

@section('title', 'Edit Customer')

@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
        <h2>Edit Customer</h2>
        <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-accent">Back to Profile</a>
    </div>

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="admin-card" style="border-left:4px solid #b40000;margin-bottom:16px;">
            <strong>There were some problems with your input:</strong>
            <ul style="margin-top:8px; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST" class="admin-card" style="max-width:980px;">
        @csrf
        @method('PUT')

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
            {{-- Company --}}
            <div>
                <label for="company_name" style="font-weight:600;">Company <span style="color:#b40000">*</span></label>
                <input id="company_name" name="company_name" type="text" required
                       value="{{ old('company_name', $customer->company_name) }}"
                       class="sl-input">
            </div>

            {{-- Contact --}}
            <div>
                <label for="contact_name" style="font-weight:600;">Contact</label>
                <input id="contact_name" name="contact_name" type="text"
                       value="{{ old('contact_name', $customer->contact_name) }}"
                       class="sl-input">
            </div>

            {{-- Email --}}
            <div>
                <label for="email" style="font-weight:600;">Email</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email', $customer->email) }}"
                       class="sl-input">
            </div>

            {{-- Mobile --}}
            <div>
                <label for="mobile_number" style="font-weight:600;">Mobile</label>
                <input id="mobile_number" name="mobile_number" type="text"
                       value="{{ old('mobile_number', $customer->mobile_number) }}"
                       class="sl-input">
            </div>

            {{-- Landline --}}
            <div>
                <label for="landline_number" style="font-weight:600;">Landline</label>
                <input id="landline_number" name="landline_number" type="text"
                       value="{{ old('landline_number', $customer->landline_number) }}"
                       class="sl-input">
            </div>

            {{-- Status --}}
            <div style="display:flex;align-items:center;gap:10px;margin-top:26px;">
                <input id="setup_completed" name="setup_completed" type="checkbox" value="1"
                       {{ old('setup_completed', (int)($customer->setup_completed ?? 0)) ? 'checked' : '' }}>
                <label for="setup_completed" style="user-select:none;">Mark as active (setup completed)</label>
            </div>

            {{-- Account tools --}}
            <div class="admin-card" style="max-width:980px; margin-top:18px; display:flex; align-items:center; justify-content:space-between; gap:16px;">
                <div>
                    <div style="font-weight:700; margin-bottom:4px;">Account Tools</div>
                    <div style="color:#6b7a89;">
                        Send a password reset email to: <strong>{{ $customer->email }}</strong>
                    </div>
                </div>

                <form action="{{ route('admin.customers.sendReset', $customer->id) }}" method="POST" onsubmit="return confirm('Send a password reset email to {{ $customer->email }}?');">
                    @csrf
                    <button type="submit" class="btn btn-primary">Send Password Reset</button>
                </form>
            </div>

            {{-- flash message helper (optional if you don’t already show it on this page) --}}
            @if (session('status'))
            <div class="admin-card" style="border-left:4px solid #2CBFAE;margin-top:12px;max-width:980px;">
                {{ session('status') }}
            </div>
            @endif

            {{-- Notes (full width) --}}
            <div style="grid-column:1 / -1;">
                <label for="notes" style="font-weight:600;">Notes</label>
                <textarea id="notes" name="notes" rows="5" class="sl-input" style="resize:vertical;">{{ old('notes', $customer->notes) }}</textarea>
            </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:18px;">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-accent">Cancel</a>
        </div>
    </form>

    {{-- Tiny input style helper so it matches admin theme --}}
    <style>
        .sl-input{
            width:100%;
            padding:.65rem .8rem;
            border:1px solid rgba(10,42,77,.15);
            border-radius:8px;
            background:#fff;
            outline:none;
            transition:border-color .15s ease, box-shadow .15s ease;
        }
        .sl-input:focus{
            border-color:#2CBFAE;
            box-shadow:0 0 0 3px rgba(44,191,174,.15);
        }
    </style>
@endsection
