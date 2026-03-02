@extends('marketing.admin.layout')

@section('content')

<h1 style="margin-bottom:20px;">Create Campaign</h1>

@if ($errors->any())
    <div style="background:#ffecec;border:1px solid #ffb3b3;padding:12px;border-radius:8px;margin-bottom:20px;">
        <strong>Validation failed:</strong>
        <ul style="margin:10px 0 0 18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('error'))
    <div style="background:#ffecec;border:1px solid #ffb3b3;padding:12px;border-radius:8px;margin-bottom:20px;">
        <strong>Error:</strong> {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div style="background:#e6f7ef;border:1px solid #b7e2c9;padding:12px;border-radius:8px;margin-bottom:20px;">
        <strong>Success:</strong> {{ session('success') }}
    </div>
@endif

<form method="POST" action="{{ route('marketing.admin.campaigns.store') }}">
    @csrf

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Campaign Name (internal)</label>
        <input type="text" name="name" required value="{{ old('name') }}"
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
        <div style="font-size:12px;color:#666;margin-top:6px;">
            Example: “March 2026 AV Promo”
        </div>
    </div>

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Email Subject</label>
        <input type="text" name="subject" required value="{{ old('subject') }}"
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
    </div>

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Body HTML</label>
        <textarea name="body_html" rows="14" required
                  style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">{{ old('body_html') }}</textarea>

        <div style="font-size:12px;color:#666;margin-top:8px;line-height:1.4;">
            Tip: Start simple while testing:
            <code style="background:#f1f1f1;padding:2px 6px;border-radius:6px;">&lt;h2&gt;Hello&lt;/h2&gt;&lt;p&gt;Test&lt;/p&gt;</code>
        </div>
    </div>

    <button type="submit" class="btn-primary">
        Save Campaign
    </button>

    <a href="{{ route('marketing.admin.campaigns') }}" style="margin-left:12px;text-decoration:none;">
        Cancel
    </a>
</form>

@endsection