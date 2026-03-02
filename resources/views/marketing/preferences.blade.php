<!-- Marketing Page: Manage Preferences -->
<h2>Manage Email Preferences</h2>

@if(!empty($saved))
    <p><strong>Preferences saved.</strong></p>
@endif

<p>Choose which emails you want to receive for {{ $email }}.</p>

<form method="POST" action="{{ route('marketing.preferences.update', $token) }}">
    @csrf

    <div style="margin:12px 0;">
        <label>
            <input type="checkbox" name="pref_sl" value="1" {{ ($sl && $sl->status === 'subscribed') ? 'checked' : '' }}>
            SharpLync updates
        </label>
    </div>

    <div style="margin:12px 0;">
        <label>
            <input type="checkbox" name="pref_sf" value="1" {{ ($sf && $sf->status === 'subscribed') ? 'checked' : '' }}>
            SharpFleet updates
        </label>
    </div>

    <button type="submit" style="background:#0ea5e9;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
        Save Preferences
    </button>
</form>
