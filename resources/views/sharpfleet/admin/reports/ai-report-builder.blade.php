@extends('layouts.sharpfleet')

@section('title', 'AI Report Builder (Beta)')

@section('sharpfleet-content')

<style>
    .sf-ai-report-card {
        border: 1px solid rgba(10, 42, 77, 0.12);
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 10px 18px rgba(10, 42, 77, 0.08);
    }
    .sf-ai-report-card .card-header {
        background: #0A2A4D;
        color: #ffffff;
        border-bottom: 0;
        border-radius: 14px 14px 0 0;
        padding-left: 24px;
    }
    .sf-ai-report-card .card-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }
    .sf-ai-result {
        background: #f7fafc;
        border: 1px solid rgba(10, 42, 77, 0.08);
        border-radius: 12px;
        padding: 16px;
    }
    .sf-ai-result h3 {
        font-size: 16px;
        margin-bottom: 8px;
        color: #0A2A4D;
    }
    .sf-ai-result h4 {
        font-size: 14px;
        margin: 14px 0 6px;
        color: #0A2A4D;
    }
    .sf-ai-result ul {
        margin: 0 0 0 16px;
        padding: 0;
    }
    .sf-ai-result li {
        margin-bottom: 6px;
        color: #0A2A4D;
    }
    .sf-ai-meta {
        font-size: 12px;
        color: rgba(10, 42, 77, 0.7);
    }
</style>

<div class="container">
    <div class="page-header mb-3">
        <div class="flex-between">
            <div>
                <h1 class="page-title">AI Report Builder (Beta)</h1>
                <p class="page-description">
                    Generate tailored fleet reports using AI-assisted analysis. Designed for advanced users to explore data beyond standard reports.
                </p>
            </div>
            <a href="/app/sharpfleet/admin/reports" class="btn btn-primary">Back to reports</a>
        </div>
    </div>

    <div class="card sf-ai-report-card mb-3">
        <div class="card-header">
            <h2>What would you like to report on?</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ url('/app/sharpfleet/admin/reports/ai-report-builder') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Report request</label>
                    <textarea name="prompt"
                              rows="5"
                              class="form-control"
                              placeholder="e.g. Show weekly utilisation by branch, identify underused vehicles, and highlight any vehicles without trips in the last 14 days.">{{ old('prompt', $prompt ?? '') }}</textarea>
                    @error('prompt')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn-sf-navy btn-sm">Generate report</button>
                <div class="sf-ai-meta mt-2">
                    Beta features may change and are not recommended for formal reporting at this stage.
                </div>
            </form>
        </div>
    </div>

    @if(!empty($result))
        <div class="card sf-ai-report-card">
            <div class="card-header">
                <h2>Generated report</h2>
            </div>
            <div class="card-body">
                <div class="sf-ai-result">
                    <h3>{{ $result['title'] ?? 'AI Report' }}</h3>
                    <div class="sf-ai-meta mb-2">{{ $result['subtitle'] ?? '' }}</div>
                    <div class="sf-ai-meta mb-3">Date range: {{ $result['date_range'] ?? '—' }}</div>

                    <h4>Summary</h4>
                    <ul>
                        <li>Total trips: {{ $result['totals']['total_trips'] ?? 0 }}</li>
                        <li>Total distance: {{ $result['totals']['total_distance'] ?? '0' }}</li>
                        <li>Total drive time: {{ $result['totals']['total_drive_time'] ?? '0h 0m' }}</li>
                        <li>Vehicle used most: {{ $result['vehicle_used_most'] ?? '—' }}</li>
                        <li>Purpose: {{ $result['purpose'] ?? '—' }}</li>
                        <li>Top customer visited: {{ $result['top_customer'] ?? '—' }}</li>
                    </ul>

                    <h4>Vehicles used</h4>
                    @if(!empty($result['vehicles_used']))
                        <ul>
                            @foreach($result['vehicles_used'] as $vehicle)
                                <li>{{ $vehicle }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="sf-ai-meta">No vehicles found.</div>
                    @endif

                    <h4>Trips</h4>
                    @if(!empty($result['trips']))
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Started</th>
                                        <th>Ended</th>
                                        <th>Vehicle</th>
                                        <th class="text-end">Distance</th>
                                        <th class="text-end">Duration</th>
                                        <th>Purpose</th>
                                        <th>Customer</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($result['trips'] as $trip)
                                        <tr>
                                            <td>{{ $trip['started_at'] ?? '—' }}</td>
                                            <td>{{ $trip['ended_at'] ?? '—' }}</td>
                                            <td>{{ $trip['vehicle'] ?? '—' }}</td>
                                            <td class="text-end">{{ $trip['distance'] ?? '—' }}</td>
                                            <td class="text-end">{{ $trip['duration'] ?? '—' }}</td>
                                            <td>{{ $trip['purpose'] ?? '' }}</td>
                                            <td>{{ $trip['customer'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="sf-ai-meta">No trips found for the selected target.</div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

@endsection
