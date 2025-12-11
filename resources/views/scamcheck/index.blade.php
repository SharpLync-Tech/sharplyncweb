@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/scamchecker.css') }}">
@endpush

@section('title', 'SharpLync Scam Checker')

@section('content')
<div style="max-width:1100px; margin:0 auto; padding-top:40px;">

    <h2>SharpLync Scam Checker (Test Page)</h2>

    <form method="POST" action="/scam-checker" enctype="multipart/form-data">
        @csrf

        <p>Paste text OR upload an email (.eml/.msg/.txt):</p>

        <textarea name="message" rows="10">@if(isset($input)){{ $input }}@endif</textarea>

        <br><br>

        <input type="file" name="file">

        <br><br>

        <button type="submit" class="sc-btn">Check Message</button>
    </form>

    {{-- ============================
         RESULTS
    ============================ --}}
    @if(isset($result))
        <div class="result-container" style="margin-top:40px;">
            <h3>Scam Analysis Result</h3>

            {{-- Azure error --}}
            @if(is_array($result) && isset($result['error']))
                <div class="result-box danger">
                    <strong>Azure Error:</strong>
                    <pre>{{ print_r($result, true) }}</pre>
                </div>

            @elseif(is_string($result))

                @php
                    // Try JSON first
                    $json = json_decode($result, true);
                    $isJson = json_last_error() === JSON_ERROR_NONE && is_array($json);

                    if ($isJson) {
                        $verdict     = ucfirst($json['verdict'] ?? '');
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

                            if (stripos($trim, 'Verdict:') === 0) { $verdict = ucfirst(trim(substr($trim, 8))); $mode=null; continue; }
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

                        $scoreNum = $score !== '' ? (int) filter_var($score, FILTER_SANITIZE_NUMBER_INT) : null;

                        $severityClass =
                            $scoreNum >= 70 ? 'danger' :
                            ($scoreNum >= 40 ? 'sus' : 'safe');
                    }
                @endphp

                {{-- JSON MODE --}}
                @if($isJson)
                    <div class="result-box {{ $severityClass }}">
                        <p><span class="value">Verdict:</span> {{ $verdict }}</p>
                        <p><span class="value">Risk Score:</span> {{ is_numeric($score) ? $score : 'N/A' }}</p>

                        <div class="section-title">Summary</div>
                        <p>{!! nl2br(e($summary)) !!}</p>

                        <div class="section-title">Red Flags</div>
                        @if(count($redFlags))
                            <ul class="red-flag-list">
                                @foreach($redFlags as $flag)
                                    <li>{{ $flag }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>No major red flags detected.</p>
                        @endif

                        <div class="section-title">Recommended Action</div>
                        <p>{!! nl2br(e($recommended)) !!}</p>
                    </div>

                {{-- LEGACY MODE --}}
                @else
                    <div class="result-box {{ $severityClass }}">
                        <p><span class="value">Verdict:</span> {{ $verdict }}</p>
                        <p><span class="value">Risk Score:</span> {{ $scoreNum ?? 'N/A' }}</p>

                        <div class="section-title">Summary</div>
                        <p>{!! nl2br(e($summary)) !!}</p>

                        <div class="section-title">Red Flags</div>
                        @if(count($redFlags))
                            <ul class="red-flag-list">
                                @foreach($redFlags as $flag)
                                    <li>{{ $flag }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>No major red flags detected.</p>
                        @endif

                        <div class="section-title">Recommended Action</div>
                        <p>{!! nl2br(e($recommended)) !!}</p>
                    </div>
                @endif

            @endif
        </div>

        {{-- ============================
             AUTO SCROLL TO RESULTS
        ============================ --}}
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const el = document.querySelector('.result-container');
                if (el) {
                    setTimeout(() => {
                        el.scrollIntoView({ behavior: "smooth", block: "start" });
                    }, 300);
                }
            });
        </script>

    @endif {{-- end result --}}
</div>
@endsection
