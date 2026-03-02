<!-- Marketing Page: Manage Preferences -->
@extends('layouts.base')

@section('title', 'Manage Email Preferences')

@section('content')
<section class="fade-section" style="padding:60px 0;">
    <div style="max-width:720px;margin:0 auto;padding:0 20px;">
        <div style="background:#ffffff;border-radius:16px;box-shadow:0 12px 30px rgba(10,42,77,0.08);padding:32px;">
            <h2 style="margin:0 0 10px 0;">Manage Email Preferences</h2>

            @if(!empty($saved))
                <div style="background:#e6f7ef;border:1px solid #b7e2c9;padding:10px 12px;border-radius:8px;margin:12px 0;">
                    <strong>Preferences saved.</strong>
                </div>
            @endif

            <p style="margin:0 0 18px 0;color:#5b6b7a;">
                Choose which emails you want to receive for <strong>{{ $email }}</strong>.
            </p>

            <form method="POST" action="{{ route('marketing.preferences.update', $token) }}">
                @csrf

                <label style="display:flex;gap:10px;align-items:flex-start;margin:12px 0;">
                    <input type="checkbox" name="pref_sl" value="1" {{ ($sl && $sl->status === 'subscribed') ? 'checked' : '' }}>
                    <span>
                        <strong>SharpLync updates</strong><br>
                        <span style="color:#6b7a89;">Security news, service updates, and product announcements.</span>
                    </span>
                </label>

                <label style="display:flex;gap:10px;align-items:flex-start;margin:12px 0;">
                    <input type="checkbox" name="pref_sf" value="1" {{ ($sf && $sf->status === 'subscribed') ? 'checked' : '' }}>
                    <span>
                        <strong>SharpFleet updates</strong><br>
                        <span style="color:#6b7a89;">Fleet product updates, features, and tips.</span>
                    </span>
                </label>

                <button type="submit" style="background:#0A2A4D;color:#fff;border:none;padding:12px 18px;border-radius:8px;cursor:pointer;">
                    Save Preferences
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
