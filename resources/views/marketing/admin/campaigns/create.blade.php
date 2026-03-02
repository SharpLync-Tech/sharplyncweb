@extends('marketing.admin.layout')

@section('content')

<h1 style="margin-bottom:20px;">Create Campaign</h1>

<form method="POST" action="{{ route('marketing.admin.campaigns.store') }}">
    @csrf

    <div style="margin-bottom:20px;">
        <label>Subject</label><br>
        <input type="text" name="subject" required
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
    </div>

    <div style="margin-bottom:20px;">
        <label>Body HTML</label><br>
        <textarea name="body_html" rows="12" required
                  style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;"></textarea>
    </div>

    <button type="submit" class="btn-primary">
        Save Campaign
    </button>

</form>

@endsection