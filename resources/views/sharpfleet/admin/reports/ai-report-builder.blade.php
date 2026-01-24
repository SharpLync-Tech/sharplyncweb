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
            <a href="/app/sharpfleet/admin/reports" class="btn btn-outline-secondary">Back to reports</a>
        </div>
    </div>

    <div class="card sf-ai-report-card mb-3">
        <div class="card-header">
            <h2>Describe the report you want</h2>
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
                <button type="submit" class="btn btn-primary">Generate report</button>
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
                    @if(!empty($result['summary']))
                        <p>{{ $result['summary'] }}</p>
                    @endif

                    @foreach(($result['sections'] ?? []) as $section)
                        <h4>{{ $section['heading'] ?? 'Overview' }}</h4>
                        @if(!empty($section['bullets']))
                            <ul>
                                @foreach($section['bullets'] as $bullet)
                                    <li>{{ $bullet }}</li>
                                @endforeach
                            </ul>
                        @endif
                    @endforeach

                    @if(!empty($result['key_metrics']))
                        <h4>Key metrics</h4>
                        <ul>
                            @foreach($result['key_metrics'] as $metric)
                                <li>{{ $metric }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @if(!empty($result['recommended_filters']))
                        <h4>Recommended filters</h4>
                        <ul>
                            @foreach($result['recommended_filters'] as $filter)
                                <li>{{ $filter }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @if(!empty($result['caveats']))
                        <h4>Notes</h4>
                        <ul>
                            @foreach($result['caveats'] as $note)
                                <li>{{ $note }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

@endsection
