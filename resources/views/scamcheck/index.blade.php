@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/scamchecker.css') }}">
@endpush

@section('title', 'SharpLync Scam Checker')

@section('content')
<div class="scam-page">

    {{-- ===========================
         SIMPLE HERO SECTION
    ============================ --}}
    <section class="scam-hero">
        <div class="scam-hero-inner">
            <h1>SharpLync Scam Checker</h1>
            <p class="scam-hero-sub">
                Paste an email or upload a file — we’ll analyse it for signs of phishing, scams, 
                or suspicious behaviour using advanced AI-driven checks.
            </p>
        </div>
    </section>


    {{-- ===========================
         FORM SECTION
    ============================ --}}
    <section class="scam-section">
        <div class="scam-section-inner">

            <form method="POST" action="/scam-checker" enctype="multipart/form-data" class="scam-form">
                @csrf

                <label class="scam-label">Paste text OR upload an email (.eml/.msg/.txt):</label>

                <textarea name="message" rows="10" class="scam-input">
@if(isset($input)){{ $input }}@endif
</textarea>

                <div class="scam-file-wrap">
                    <input type="file" name="file">
                </div>

                <button type="submit" class="scam-btn-primary">Check Message</button>
            </form>

        </div>
    </section>


    {{-- ===========================
         RESULT SECTION
    ============================ --}}
    @if(isset($result))
    <section class="scam-section">
        <div class="scam-section-inner">

            <h2 class="scam-section-title">Scam Analysis Result</h2>

            @if(is_array($result) && isset($result['error']))
                <div class="scam-result-card danger">
                    <strong>Azure Error:</strong>
                    <pre>{{ print_r($result, true) }}</pre>
                </div>

            @elseif(is_string($result))

                @php
                    // Try JSON first
                    $json = json_decode($result, true);
                    $isJson = json_last_error() === JSON_ERROR_NONE && is_array($json);

                    if ($isJson) {
                        $verdict     = $json['verdict'] ?? '';
                        $score       = $json['risk_score'] ?? 'N/A';
                        $summary     = $json['summary'] ?? '';
                        $redFlags    = $json['red_flags'] ?? [];
                        $recommended = $json['recommended_action'] ?? '';

                        if (is_numeric($score)) {
                            $scoreNum = (int)$score;
                            $severityClass =
                                $scoreNum >= 70 ? 'danger' :
                                ($scoreNum >= 40 ? 'sus' : 'safe');
                        } else {
                            $v = strtolower($verdict);
                            $severityClass =
                                str_contains($v, 'scam') ? 'danger' :
                                (str_contains($v, 'suspicious') || str_contains($v, 'unclear') ? 'sus' : 'safe');
                        }
                    } else {
                        // Legacy parsing (unchanged)
                        $lines = explode("\n", $result);
                        $verdict = '';
                        $score = '';
                        $summary = '';
                        $redFlags = [];
                        $recommended = '';
                        $mode = null;

                        foreach ($lines as $line) {
                            $trim = trim($line);
                            if ($trim === '') continue;

                            if (stripos($trim, 'Verdict:') === 0) { $verdict = trim(substr($trim, 8)); $mode=null; continue; }
                            if (stripos($trim, 'Risk Score:') === 0) { $score = trim(substr($trim, 11)); $mode=null; continue; }
                            if (stripos($trim, 'Summary:') === 0) { $summary = trim(substr($trim, 8)); $mode='summary'; continue; }
                            if (stripos($trim, 'Red Flags:') === 0 || stripos($trim,'Reasons:')===0) { $mode='flags'; continue; }
                            if (stripos($trim, 'Recommended Action:') === 0) { $recommended = trim(substr($trim, 20)); $mode='recommended'; continue; }
                            if (stripos($trim, 'Recommendation:') === 0) { $recommended = trim(substr($trim, 13)); $mode='recommended'; continue; }

                            if ($mode === 'summary') { $summary .= "\n".$trim; continue; }
                            if ($mode === 'flags') {
                                if (strpos($trim, '-') === 0) { $redFlags[] = ltrim(substr($trim, 1)); continue; }
                                if (preg_match('/^\d+\.\s*(.+)$/', $trim, $m)) { $redFlags[] = $m[1]; continue; }
                            }
                            if ($mode === 'recommended') { $recommended .= "\n".$trim; continue; }
                        }

                        if ($summary === '' && count($redFlags)) {
                            $summary = $redFlags[0];
                        }

                        $scoreNum = $score !== '' ?
                            (int) filter_var($score, FILTER_SANITIZE_NUMBER_INT)
                            : null;

                        $severityClass =
                            $scoreNum >= 70 ? 'danger' :
                            ($scoreNum >= 40 ? 'sus' : 'safe');
                    }
                @endphp

                {{-- JSON MODE --}}
                @if($isJson)
                    <div class="scam-result-card {{ $severityClass }}">
                        <p><strong>Verdict:</strong> {{ $verdict }}</p>
                        <p><strong>Risk Score:</strong> {{ is_numeric($score) ? $score : 'N/A' }}</p>

                        <h4 class="scam-subtitle">Summary</h4>
                        <p>{!! nl2br(e($summary)) !!}</p>

                        <h4 class="scam-subtitle">Red Flags</h4>
                        @if(count($redFlags))
                            <ul class="scam-flag-list">
                                @foreach($redFlags as $flag)
                                    <li>{{ $flag }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>No major red flags detected.</p>
                        @endif

                        <h4 class="scam-subtitle">Recommended Action</h4>
                        <p>{!! nl2br(e($recommended)) !!}</p>
                    </div>

                {{-- LEGACY --}}
                @else
                    <div class="scam-result-card {{ $severityClass }}">
                        <p><strong>Verdict:</strong> {{ $verdict }}</p>
                        <p><strong>Risk Score:</strong> {{ $scoreNum ?? 'N/A' }}</p>

                        <h4 class="scam-subtitle">Summary</h4>
                        <p>{!! nl2br(e($summary)) !!}</p>

                        <h4 class="scam-subtitle">Red Flags</h4>
                        @if(count($redFlags))
                            <ul class="scam-flag-list">
                                @foreach($redFlags as $flag)
                                    <li>{{ $flag }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>No major red flags detected.</p>
                        @endif

                        <h4 class="scam-subtitle">Recommended Action</h4>
                        <p>{!! nl2br(e($recommended)) !!}</p>
                    </div>
                @endif

            @endif

        </div>
    </section>
    @endif

</div>
@endsection
