<!-- Marketing Page: Campaign Index -->
@extends('marketing.admin.layout')

@section('content')

@php
    $brandScope = $brandScope ?? 'both';
@endphp

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

@if(session('error'))
    <div class="card" style="background:#ffecec;border:1px solid #ffb3b3;">
        <strong>Error:</strong> {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div class="card" style="background:#e6f7ef;border:1px solid #b7e2c9;">
        <strong>Success:</strong> {{ session('success') }}
    </div>
@endif

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
                <th>Brand</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Scheduled</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        @foreach($campaigns as $campaign)
            <tr>
                <td>{{ $campaign->id }}</td>
                <td>{{ strtoupper($campaign->brand) }}</td>
                <td>{{ $campaign->subject }}</td>
                <td>
                    @if($campaign->status === 'draft')
                        <span class="badge badge-draft">Draft</span>
                    @elseif($campaign->status === 'pending_review')
                        <span class="badge badge-draft">Pending Review</span>
                    @elseif($campaign->status === 'approved')
                        <span class="badge badge-sent">Approved</span>
                    @elseif($campaign->status === 'scheduled')
                        <span class="badge badge-sent">Scheduled</span>
                    @elseif($campaign->status === 'sent')
                        <span class="badge badge-sent">Sent</span>
                    @endif
                </td>
                <td>{{ $campaign->scheduled_at }}</td>
                <td>{{ $campaign->created_at }}</td>
                <td>
                    <a href="{{ route('marketing.admin.campaigns.preview', $campaign->id) }}" target="_blank" style="margin-right:8px;">Preview</a>
                    <form method="POST" action="{{ route('marketing.admin.campaigns.test', $campaign->id) }}" style="display:inline;margin-right:6px;">
                        @csrf
                        <button type="submit" class="btn-send" style="background:#0ea5e9;">Test</button>
                    </form>

                    @if($campaign->status === 'draft')
                        <a href="{{ route('marketing.admin.campaigns.edit', $campaign->id) }}" style="margin-right:8px;">Edit</a>
                        <form method="POST" action="{{ route('marketing.admin.campaigns.submit', $campaign->id) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn-send">Submit</button>
                        </form>
                        <form method="POST" action="{{ route('marketing.admin.campaigns.delete', $campaign->id) }}" style="display:inline;margin-left:6px;">
                            @csrf
                            <button type="submit" class="btn-send" style="background:#b40000;">Delete</button>
                        </form>
                    @elseif($campaign->status === 'pending_review')
                        <a href="{{ route('marketing.admin.campaigns.edit', $campaign->id) }}" style="margin-right:8px;">Edit</a>
                        <form method="POST" action="{{ route('marketing.admin.campaigns.approve', $campaign->id) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn-send">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('marketing.admin.campaigns.delete', $campaign->id) }}" style="display:inline;margin-left:6px;">
                            @csrf
                            <button type="submit" class="btn-send" style="background:#b40000;">Delete</button>
                        </form>
                    @elseif(in_array($campaign->status, ['approved', 'scheduled']))
                        <form method="POST" action="{{ route('marketing.admin.campaigns.schedule', $campaign->id) }}" style="display:inline;">
                            @csrf
                            <input type="datetime-local" name="scheduled_at" value="{{ $campaign->scheduled_at ? $campaign->scheduled_at->format('Y-m-d\TH:i') : '' }}" style="margin-right:6px;">
                            <button type="submit" class="btn-send">Schedule</button>
                        </form>
                        <form method="POST" action="{{ route('marketing.admin.campaigns.send', $campaign->id) }}" style="display:inline;margin-left:6px;">
                            @csrf
                            <button type="submit" class="btn-send">Send Now</button>
                        </form>
                        <form method="POST" action="{{ route('marketing.admin.campaigns.delete', $campaign->id) }}" style="display:inline;margin-left:6px;">
                            @csrf
                            <button type="submit" class="btn-send" style="background:#b40000;">Delete</button>
                        </form>
                    @elseif($campaign->status === 'sent')
                        <form method="POST" action="{{ route('marketing.admin.campaigns.resend', $campaign->id) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn-send">Resend</button>
                        </form>
                    @else
                        --
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>

@endsection
