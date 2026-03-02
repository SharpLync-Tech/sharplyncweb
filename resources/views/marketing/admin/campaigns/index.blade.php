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
                <td>{{ $campaign->scheduled_at ? $campaign->scheduled_at->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ $campaign->created_at ? $campaign->created_at->format('d/m/Y H:i') : '-' }}</td>
                <td>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;">
                        <a href="{{ route('marketing.admin.campaigns.preview', $campaign->id) }}" target="_blank">Preview</a>
                        <form method="POST" action="{{ route('marketing.admin.campaigns.test', $campaign->id) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn-send" style="background:#0ea5e9;">Test</button>
                        </form>

                        @if($campaign->status === 'draft')
                            <form method="POST" action="{{ route('marketing.admin.campaigns.submit', $campaign->id) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-send">Submit</button>
                            </form>
                        @elseif($campaign->status === 'pending_review')
                            <form method="POST" action="{{ route('marketing.admin.campaigns.approve', $campaign->id) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-send">Approve</button>
                            </form>
                        @elseif(in_array($campaign->status, ['approved', 'scheduled']))
                            <form method="POST" action="{{ route('marketing.admin.campaigns.send', $campaign->id) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-send">Send Now</button>
                            </form>
                        @elseif($campaign->status === 'sent')
                            <form method="POST" action="{{ route('marketing.admin.campaigns.resend', $campaign->id) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-send">Resend</button>
                            </form>
                        @endif

                        <details style="display:inline-block;position:relative;">
                            <summary style="cursor:pointer;list-style:none;">More ▾</summary>
                            <div style="position:absolute;right:0;top:24px;min-width:180px;background:#fff;border:1px solid #e6e6e6;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,0.08);padding:10px;z-index:10;">
                                @if(in_array($campaign->status, ['draft', 'pending_review']))
                                    <a href="{{ route('marketing.admin.campaigns.edit', $campaign->id) }}" style="display:block;margin-bottom:8px;">Edit</a>
                                @endif

                                @if(in_array($campaign->status, ['approved', 'scheduled']))
                                    <form method="POST" action="{{ route('marketing.admin.campaigns.schedule', $campaign->id) }}" style="display:block;margin-bottom:8px;">
                                        @csrf
                                        <input type="datetime-local" name="scheduled_at" value="{{ $campaign->scheduled_at ? $campaign->scheduled_at->format('Y-m-d\\TH:i') : '' }}" style="width:100%;margin-bottom:6px;">
                                        <button type="submit" class="btn-send" style="width:100%;">Schedule</button>
                                    </form>
                                @endif

                                @if($campaign->status !== 'sent')
                                    <form method="POST" action="{{ route('marketing.admin.campaigns.delete', $campaign->id) }}" style="display:block;">
                                        @csrf
                                        <button type="submit" class="btn-send" style="background:#b40000;width:100%;">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </details>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>

@endsection
