<!DOCTYPE html>
<html>
<head>
    <title>SharpLync Scam Checker</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            line-height: 1.45;
            background: #fafafa;
        }

        textarea {
            width: 100%;
            max-width: 900px;
            padding: 8px;
            font-size: 14px;
        }

        .result-container {
            margin-top: 25px;
            max-width: 900px;
        }

        .result-box {
            background: #ffffff;
            border: 1px solid #cccccc;
            padding: 16px;
            border-radius: 6px;
            white-space: pre-wrap;
        }

        .section-title {
            font-size: 17px;
            font-weight: bold;
            margin-top: 14px;
            margin-bottom: 6px;
        }

        .red-flag-list {
            margin-left: 10px;
            padding-left: 10px;
            border-left: 3px solid #c62828;
        }

        .safe {
            border-left: 4px solid #2e7d32;
        }

        .sus {
            border-left: 4px solid #ed6c02;
        }

        .danger {
            border-left: 4px solid #c62828;
        }

        .value {
            font-weight: bold;
        }

        .raw-output {
            margin-top: 20px;
            background: #fff3cd;
            padding: 12px;
            border: 1px solid #ffecb5;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<h2>SharpLync Scam Checker (Test Page)</h2>

<form method="POST" action="/scam-checker" enctype="multipart/form-data">
    @csrf

    <p>Paste text OR upload an email (.eml/.msg/.txt):</p>

    <textarea name="message" rows="10">@if(isset($input)){{ $input }}@endif</textarea>

    <br><br>

    <input type="file" name="file">

    <br><br>

    <button type="submit">Check Message</button>
</form>

@if(isset($result))

    <div class="result-container">
        <h3>Scam Analysis Result</h3>

        {{-- Azure Error --}}
        @if(is_array($result) && isset($result['error']))
            <div class="raw-output">
                <strong>Azure Error:</strong>
                <pre>{{ print_r($result, true) }}</pre>
            </div>

        {{-- ========================= --}}
        {{-- PARSING BLOCK STARTS HERE --}}
        {{-- ========================= --}}
        @elseif(is_string($result))

            @php
                // Try JSON first
                $json = json_decode($result, true);

                $isJson = json_last_error() === JSON_ERROR_NONE && is_array($json);

                if ($isJson) {
                    // JSON mode
                    $verdict     = $json['verdict'] ?? '';
                    $score       = $json['risk_score'] ?? 'N/A';
                    $summary     = $json['summary'] ?? '';
                    $redFlags    = $json['red_flags'] ?? [];
                    $recommended = $json['recommended_action'] ?? '';

                    // Severity based on JSON
                    if (is_numeric($score)) {
                        $scoreNum = (int)$score;
                        $severityClass =
                            $scoreNum >= 70 ? 'danger' :
                            ($scoreNum >= 40 ? 'sus' : 'safe');
                    } else {
                        $v = strtolower($verdict);
                        if (str_contains($v, 'scam')) {
                            $severityClass = 'danger';
                        } elseif (str_contains($v, 'suspicious') || str_contains($v, 'unclear')) {
                            $severityClass = 'sus';
                        } else {
                            $severityClass = 'safe';
                        }
                    }
                } else {

                    // ==============================
                    // ORIGINAL LEGACY TEXT PARSER
                    // (kept EXACTLY as you had it)
                    // ==============================

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

                        if (stripos($trim, 'Verdict:') === 0) {
                            $verdict = trim(substr($trim, 8));
                            $mode = null; continue;
                        }
                        if (stripos($trim, 'Risk Score:') === 0) {
                            $score = trim(substr($trim, 11));
                            $mode = null; continue;
                        }
                        if (stripos($trim, 'Summary:') === 0) {
                            $summary = trim(substr($trim, 8));
                            $mode = 'summary'; continue;
                        }
                        if (stripos($trim, 'Red Flags:') === 0) {
                            $mode = 'flags'; continue;
                        }
                        if (stripos($trim, 'Reasons:') === 0) {
                            $mode = 'flags'; continue;
                        }
                        if (stripos($trim, 'Recommended Action:') === 0) {
                            $recommended = trim(substr($trim, 20));
                            $mode = 'recommended'; continue;
                        }
                        if (stripos($trim, 'Recommendation:') === 0) {
                            $recommended = trim(substr($trim, 13));
                            $mode = 'recommended'; continue;
                        }
                        if ($mode === 'summary') {
                            $summary .= "\n" . $trim; continue;
                        }
                        if ($mode === 'flags') {
                            if (strpos($trim, '-') === 0) {
                                $redFlags[] = ltrim(substr($trim, 1));
                                continue;
                            }
                            if (preg_match('/^\d+\.\s*(.+)$/', $trim, $m)) {
                                $redFlags[] = $m[1];
                                continue;
                            }
                        }
                        if ($mode === 'recommended') {
                            $recommended .= "\n" . $trim;
                            continue;
                        }
                    }

                    if ($summary === '' && count($redFlags) > 0) {
                        $summary = $redFlags[0];
                    }

                    $scoreNum = null;
                    if ($score !== '') {
                        $scoreNum = (int) filter_var($score, FILTER_SANITIZE_NUMBER_INT);
                    }

                    if ($scoreNum !== null && $scoreNum > 0) {
                        $severityClass =
                            $scoreNum >= 70 ? 'danger' :
                            ($scoreNum >= 40 ? 'sus' : 'safe');
                    } else {
                        $v = strtolower($verdict);
                        if (str_contains($v, 'phishing') || str_contains($v, 'scam')) {
                            $severityClass = 'danger';
                        } elseif (str_contains($v, 'suspicious') || str_contains($v, 'unclear')) {
                            $severityClass = 'sus';
                        } else {
                            $severityClass = 'safe';
                        }
                    }

                } // end isJson
            @endphp

            {{-- ============================================================ --}}
            {{-- JSON MODE OUTPUT (preferred)                                 --}}
            {{-- ============================================================ --}}
            @if($isJson)
                <div class="result-box {{ $severityClass }}">

                    <p><span class="value">Verdict:</span> {{ $verdict }}</p>
                    <p><span class="value">Risk Score:</span> {{ is_numeric($score) ? $score : 'N/A' }}</p>

                    <div class="section-title">Summary</div>
                    <p>{!! nl2br(e($summary)) !!}</p>

                    <div class="section-title">Red Flags</div>
                    @if(count($redFlags))
                        <div class="red-flag-list">
                            @foreach($redFlags as $flag)
                                <p>- {{ $flag }}</p>
                            @endforeach
                        </div>
                    @else
                        <p>No major red flags detected.</p>
                    @endif

                    <div class="section-title">Recommended Action</div>
                    <p>{!! nl2br(e($recommended)) !!}</p>

                </div>

            {{-- ============================================================ --}}
            {{-- LEGACY MODE OUTPUT (fallback — unchanged from your OG logic) --}}
            {{-- ============================================================ --}}
            @else
                <div class="result-box {{ $severityClass }}">

                    <p><span class="value">Verdict:</span> {{ $verdict }}</p>
                    <p><span class="value">Risk Score:</span> {{ $scoreNum ?? 'N/A' }}</p>

                    <div class="section-title">Summary</div>
                    <p>{!! nl2br(e($summary)) !!}</p>

                    <div class="section-title">Red Flags</div>
                    @if(count($redFlags))
                        <div class="red-flag-list">
                            @foreach($redFlags as $flag)
                                <p>- {{ trim($flag) }}</p>
                            @endforeach
                        </div>
                    @else
                        <p>No major red flags detected.</p>
                    @endif

                    <div class="section-title">Recommended Action</div>
                    <p>{!! nl2br(e($recommended)) !!}</p>

                </div>
            @endif

        @endif {{-- end parsing block --}}
    </div>
@endif

</body>
</html>
