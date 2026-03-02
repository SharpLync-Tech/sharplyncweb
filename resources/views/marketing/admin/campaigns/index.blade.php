@extends('layouts.app')

@section('content')
<div style="max-width:1100px;margin:40px auto;">

    <h1 style="margin-bottom:20px;">Marketing Campaigns</h1>

    @if(session('success'))
        <div style="background:#e6f7ef;padding:12px;border-radius:6px;margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('marketing.admin.campaigns.create') }}"
       style="display:inline-block;background:#0b1e3d;color:white;padding:10px 18px;border-radius:6px;text-decoration:none;margin-bottom:20px;">
        + Create Campaign
    </a>

    <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse:collapse;">
        <thead style="background:#0b1e3d;color:white;">
            <tr>
                <th align="left">ID</th>
                <th align="left">Subject</th>
                <th align="left">Status</th>
                <th align="left">Created</th>
                <th align="left">Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($campaigns as $campaign)
            <tr style="border-bottom:1px solid #ddd;">
                <td>{{ $campaign->id }}</td>
                <td>{{ $campaign->subject }}</td>
                <td>{{ $campaign->status }}</td>
                <td>{{ $campaign->created_at }}</td>
                <td>
                    <form method="POST"
                          action="{{ route('marketing.admin.campaigns.send', $campaign->id) }}"
                          style="display:inline;">
                        @csrf
                        <button type="submit"
                                style="background:#1f4fd8;color:white;border:none;padding:6px 12px;border-radius:4px;cursor:pointer;">
                            Send Now
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>
@endsection