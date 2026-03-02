<!-- Marketing Page: Sending Logs -->
@extends('marketing.admin.layout')

@section('content')

<div class="card">
    <h2 style="margin-top:0;">Sending Logs</h2>
    <div style="font-size:13px;color:#666;margin-bottom:12px;">
        Showing the latest {{ $logs->count() }} sends (most recent first).
    </div>

    <table>
        <thead>
            <tr>
                <th>Sent At</th>
                <th>Campaign</th>
                <th>Brand</th>
                <th>Subscriber</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td>{{ $log->sent_at }}</td>
                <td>{{ $log->campaign->subject ?? 'Unknown' }}</td>
                <td>{{ strtoupper($log->campaign->brand ?? '-') }}</td>
                <td>{{ $log->subscriber->email ?? '-' }}</td>
                <td>{{ $log->status }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5">No logs yet.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
