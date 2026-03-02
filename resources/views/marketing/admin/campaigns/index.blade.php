@extends('marketing.admin.layout')

@section('content')

<div class="stats" style="margin-bottom:30px;">
    <div class="stat-box">
        <div class="stat-number">{{ $subscriberCount }}</div>
        <div>Total Subscribers</div>
    </div>

    <div class="stat-box">
        <div class="stat-number">{{ $campaigns->count() }}</div>
        <div>Total Campaigns</div>
    </div>
</div>

<div class="card">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="margin:0;">Campaigns</h2>

        <a href="{{ route('marketing.admin.campaigns.create') }}" class="btn-primary">
            + New Campaign
        </a>
    </div>

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
                <td>
                    @if($campaign->status === 'draft')
                        <span class="badge badge-draft">Draft</span>
                    @elseif($campaign->status === 'sent')
                        <span class="badge badge-sent">Sent</span>
                    @endif
                </td>
                <td>{{ $campaign->created_at }}</td>
                <td>
                    @if($campaign->status !== 'sent')
                    <form method="POST"
                          action="{{ route('marketing.admin.campaigns.send', $campaign->id) }}"
                          style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-send">
                            Send Now
                        </button>
                    </form>
                    @else
                        —
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>

@endsection