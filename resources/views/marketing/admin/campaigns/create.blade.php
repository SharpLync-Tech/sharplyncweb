@extends('layouts.app')

@section('content')
<div style="max-width:900px;margin:40px auto;">

    <h1 style="margin-bottom:20px;">Create Campaign</h1>

    <form method="POST" action="{{ route('marketing.admin.campaigns.store') }}">
        @csrf

        <div style="margin-bottom:20px;">
            <label>Subject</label>
            <input type="text" name="subject" required
                   style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
        </div>

        <div style="margin-bottom:20px;">
            <label>Body HTML</label>
            <textarea name="body_html" rows="12" required
                      style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;"></textarea>
        </div>

        <button type="submit"
                style="background:#0b1e3d;color:white;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;">
            Save Campaign
        </button>

    </form>

</div>
@endsection