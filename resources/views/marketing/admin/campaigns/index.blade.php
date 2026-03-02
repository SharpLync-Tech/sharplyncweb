@extends('marketing.admin.layout')

@section('content')

<h1 style="margin-bottom:20px;">Marketing Campaigns</h1>

@if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
@endif

<a href="{{ route('marketing.admin.campaigns.create') }}" class="btn-primary" style="margin-bottom:20px;">
    + Create Campaign
</a>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Subject</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach($campaigns as $campaign)
        <tr>
            <td>{{ $campaign->id }}</td>
            <td>{{ $campaign->subject }}</td>
            <td>{{ $campaign->status }}</td>
            <td>{{ $campaign->created_at }}</td>
            <td>
                <form method="POST"
                      action="{{ route('marketing.admin.campaigns.send', $campaign->id) }}"
                      style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-action">
                        Send Now
                    </button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

@endsection