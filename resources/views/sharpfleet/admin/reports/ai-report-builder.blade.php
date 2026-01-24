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
        padding-top: 12px;
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
                <div class="d-flex align-items-center justify-content-between">
                    <h2>Generated report</h2>
                    <form method="POST" action="{{ url('/app/sharpfleet/admin/reports/ai-report-builder') }}">
                        @csrf
                        <input type="hidden" name="prompt" value="{{ $prompt ?? '' }}">
                        <input type="hidden" name="export" value="csv">
                        <button type="submit" class="btn btn-light btn-sm">Export CSV</button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="sf-ai-result">
                    <h3>{{ $result['title'] ?? 'AI Report' }}</h3>
                    @if(!empty($result['subtitle']))
                        <div class="sf-ai-meta mb-2">{{ $result['subtitle'] }}</div>
                    @endif
                    @if(!empty($result['date_range']))
                        <div class="sf-ai-meta mb-3">Date range: {{ $result['date_range'] }}</div>
                    @endif

                    @if(!empty($result['summary']))
                        <h4>Summary</h4>
                        <ul>
                            @foreach($result['summary'] as $item)
                                <li>{{ $item['label'] ?? '' }}: {{ $item['value'] ?? '' }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @if(!empty($result['vehicles_used']))
                        <h4>Vehicles used</h4>
                        <ul>
                            @foreach($result['vehicles_used'] as $vehicle)
                                <li>{{ $vehicle }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <h4>Results</h4>
                    @if(!empty($result['rows']) && !empty($result['columns']))
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        @foreach($result['columns'] as $column)
                                            <th class="{{ $column['align'] ?? '' }}">{{ $column['label'] ?? '' }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($result['rows'] as $row)
                                        <tr>
                                            @foreach($result['columns'] as $column)
                                                @php
                                                    $key = $column['key'] ?? null;
                                                    $value = $key ? ($row[$key] ?? '') : '';
                                                @endphp
                                                <td class="{{ $column['align'] ?? '' }}">{{ $value !== '' ? $value : '—' }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="sf-ai-meta">No results found for the selected request.</div>
                    @endif

                    @php
                        $paginator = $result['paginator'] ?? null;
                        $perPage = $paginator ? $paginator->perPage() : 10;
                    @endphp

                    @if($paginator && $paginator->lastPage() > 1)
                        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mt-3">
                            <form method="POST" action="{{ url('/app/sharpfleet/admin/reports/ai-report-builder') }}">
                                @csrf
                                <input type="hidden" name="prompt" value="{{ $prompt ?? '' }}">
                                <label class="form-label mb-0 me-2">Rows per page</label>
                                <select name="per_page" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
                                    @foreach([10, 25, 50, 100] as $size)
                                        <option value="{{ $size }}" @if($perPage == $size) selected @endif>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </form>

                            <div class="d-flex gap-2">
                                @if($paginator->currentPage() > 1)
                                    <form method="POST" action="{{ url('/app/sharpfleet/admin/reports/ai-report-builder') }}">
                                        @csrf
                                        <input type="hidden" name="prompt" value="{{ $prompt ?? '' }}">
                                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                                        <input type="hidden" name="page" value="{{ $paginator->currentPage() - 1 }}">
                                        <button type="submit" class="btn btn-secondary btn-sm">Previous</button>
                                    </form>
                                @endif
                                @if($paginator->currentPage() < $paginator->lastPage())
                                    <form method="POST" action="{{ url('/app/sharpfleet/admin/reports/ai-report-builder') }}">
                                        @csrf
                                        <input type="hidden" name="prompt" value="{{ $prompt ?? '' }}">
                                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                                        <input type="hidden" name="page" value="{{ $paginator->currentPage() + 1 }}">
                                        <button type="submit" class="btn btn-secondary btn-sm">Next</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

@endsection

