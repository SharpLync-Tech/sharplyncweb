@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/scamchecker.css') }}">
@endpush

@section('title', 'SharpLync Scam Checker')

@section('content')
<div class="sc-page">
<div style="max-width:1100px; margin:0 auto; padding-top:40px;">

    <h2 class="scam-result-title"><span class="gradient">SharpLync</span> Scam Checker</h2>

    {{-- ============================
         FORM AREA
    ============================ --}}
    <div id="form-area">
        <form method="POST" action="/scam-checker" enctype="multipart/form-data">
            @csrf

            <textarea name="message" rows="10"
                placeholder="Paste text OR upload an email (.eml/.msg/.txt):">@if(isset($input)){{ $input }}@endif</textarea>

            <br><br>
            <input type="file" name="file">
            <br><br>

            <button type="submit" class="scam-btn" id="check-btn">Check Message</button>

            {{-- Clear button --}}
            <button type="button" id="clear-btn" style="display:none;" onclick="clearScamForm()">Clear</button>
        </form>

        {{-- SINGLE LOADER — this is the correct one --}}
        <div id="scan-loader" class="scan-loader" style="display:none;">
            <div class="scan-logo">
                <svg viewBox="0 0 200 200" class="scan-svg">
                    <circle class="ring-bg" cx="100" cy="100" r="80" />
                    <circle class="ring-sweep" cx="100" cy="100" r="80" />
                    <polygon class="arrow" points="60,95 140,75 140,145" />
                </svg>
            </div>
            <p class="scan-text">Scanning for threats…</p>
        </div>
    </div> {{-- END form-area --}}

    {{-- ============================
         RESULTS SECTION
    ============================ --}}
    @if(isset($result))
        <div class="result-container" style="margin-top:40px;">
            <h3 class="scam-result-title">Scam Analysis Result</h3>

            {{-- Error --}}
            @if(is_array($result) && isset($result['error']))
                <div class="result-box danger">
                    <strong>Azure Error:</strong>
                    <pre>{{ print_r($result, true) }}</pre>
                </div>

            @else
                @php
                    // JSON?
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
                        // Legacy fallback
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
                            if (stripos($trim, 'Red Flags:') === 0) { $mode='flags'; continue; }
                            if (stripos($trim, 'Recommended Action:') === 0) { $recommended = trim(substr($trim, 20)); $mode='recommended'; continue; }

                            if ($mode === 'summary') { $summary .= "\n".$trim; continue; }
                            if ($mode === 'flags') {
                                if (strpos($trim, '-') === 0) $redFlags[] = ltrim(substr($trim, 1));
                                if (preg_match('/^\d+\.\s*(.+)$/', $trim, $m)) $redFlags[] = $m[1];
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

                {{-- Result Box --}}
                <div class="result-box {{ $severityClass }}">
                    <p>
                        <span class="value">Verdict:</span>
                        <span class="verdict-text">{{ $verdict }}</span>
                        <span class="verdict-dot {{ $severityClass }}"></span>
                    </p>

                    <p><span class="value">Risk Score:</span> {{ $score }}</p>

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
        </div>

        {{-- AFTER RESULTS: show Clear button --}}
        <script>
            document.getElementById('clear-btn').style.display = 'inline-block';
        </script>

        {{-- Auto-scroll --}}
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const el = document.querySelector('.result-container');
                if (el) {
                    setTimeout(() => el.scrollIntoView({ behavior: "smooth" }), 300);
                }
            });
        </script>

    @endif {{-- end results --}}
</div>
</div>
@endsection

{{-- ============================
     JS HANDLERS
============================ --}}
<script>
document.getElementById('check-btn').addEventListener('click', function () {
    const formArea = document.getElementById('form-area');
    const loader = document.getElementById('scan-loader');

    // Hide form elements
    formArea.classList.add("scanning");

    // Show loader
    loader.style.display = "block";
});

// Clear button logic
function clearScamForm() {
    const formArea = document.getElementById('form-area');

    // Reset fields
    document.querySelector('textarea[name="message"]').value = "";
    document.querySelector('input[type="file"]').value = null;

    // Remove results
    const resultBox = document.querySelector('.result-container');
    if (resultBox) resultBox.remove();

    // Show form again
    formArea.classList.remove("scanning");

    // Hide clear button
    document.getElementById('clear-btn').style.display = 'none';
}
</script>
