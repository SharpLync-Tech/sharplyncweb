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

{{-- ========================================================= --}}
{{-- DEBUG BLOCK — Shows raw Azure output (temporary/safe)     --}}
{{-- ========================================================= --}}
@if(isset($result))
    <div class="raw-output" style="margin-top:20px; background:#eef; padding:15px; border:1px solid #ccd;">
        <h3>Raw Output (Debug)</h3>
        <pre>{{ print_r($result, true) }}</pre>
    </div>
@endif
{{-- END DEBUG BLOCK --}}

@if(isset($result))

    <div class="result-container">
        <h3>Scam Analysis Result</h3>

        {{-- Azure Error --}}
        @if(is_array($result) && isset($result['error']))
            <div class="raw-output">
                <strong>Azure Error:</strong>
                <pre>{{ print_r($result, true) }}</pre>
            </div>

        {{-- AI structured text --}}
        @elseif(is_string($result))

            @php
                // Split into lines
                $lines = explode("\n", $result);

                $verdict = '';
                $score = '';
                $summary = '';
                $redFlags = [];
                $recommended = '';

                $mode = null;

                foreach ($lines as $line) {
                    $trim = trim($line);

                    // Skip empty lines early
                    if ($trim === '') {
                        continue;
                    }

                    // Verdict
                    if (stripos($trim, 'Verdict:') === 0) {
                        $verdict = trim(substr($trim, 8));
                        $mode = null;
                        continue;
                    }

                    // Risk Score
                    if (stripos($trim, 'Risk Score:') === 0) {
                        $score = trim(substr($trim, 11));
                        $mode = null;
                        continue;
                    }

                    // Summary
                    if (stripos($trim, 'Summary:') === 0) {
                        $summary = trim(substr($trim, 8));
                        $mode = 'summary';
                        continue;
                    }

                    // Red Flags heading (original format)
                    if (stripos($trim, 'Red Flags:') === 0) {
                        $mode = 'flags';
                        continue;
                    }

                    // Reasons heading (new format we saw)
                    if (stripos($trim, 'Reasons:') === 0) {
                        $mode = 'flags'; // treat reasons as red flags
                        continue;
                    }

                    // Recommended Action (original format)
                    if (stripos($trim, 'Recommended Action:') === 0) {
                        $recommended = trim(substr($trim, 20));
                        $mode = 'recommended';
                        continue;
                    }

                    // Recommendation (new format we saw)
                    if (stripos($trim, 'Recommendation:') === 0) {
                        $recommended = trim(substr($trim, 13));
                        $mode = 'recommended';
                        continue;
                    }

                    // Collect extra summary lines
                    if ($mode === 'summary') {
                        $summary .= "\n" . $trim;
                        continue;
                    }

                    // Collect red flags (either - bullet or numbered list "1. ...")
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

                    // Collect additional recommended action lines
                    if ($mode === 'recommended') {
                        $recommended .= "\n" . $trim;
                        continue;
                    }
                }

                // If summary is empty but we have red flags, use first red flag as a simple summary
                if ($summary === '' && count($redFlags) > 0) {
                    $summary = $redFlags[0];
                }

                // Determine severity styling
                $scoreNum = null;

                if ($score !== '') {
                    $scoreNum = (int) filter_var($score, FILTER_SANITIZE_NUMBER_INT);
                }

                if ($scoreNum !== null && $scoreNum > 0) {
                    $severityClass =
                        $scoreNum >= 70 ? 'danger' :
                        ($scoreNum >= 40 ? 'sus' : 'safe');
                } else {
                    // Fallback: classify by verdict text if no score present
                    $v = strtolower($verdict);

                    if (str_contains($v, 'phishing') || str_contains($v, 'likely scam') || str_contains($v, 'scam')) {
                        $severityClass = 'danger';
                    } elseif (str_contains($v, 'suspicious') || str_contains($v, 'unclear')) {
                        $severityClass = 'sus';
                    } else {
                        $severityClass = 'safe';
                    }
                }

                // Custom message ONLY when email is legit
                $customLegitAction = null;

                if (strtolower(trim($verdict)) === 'likely legitimate') {
                    $customLegitAction =
                        "We have checked this email and did not find any signs of phishing, fraud, or suspicious behaviour.\n\n" .
                        "However, please continue to be cautious. If anything about the email feels unusual or unexpected, let SharpLync know so we can verify it for you.\n\n" .
                        "This system provides automated analysis and is intended for informational guidance only. No automated tool can guarantee 100% accuracy.\n\n" .
                        "If you ever feel unsure, contact SharpLync and we’ll confirm the email's legitimacy.";
                }

                $scoreDisplay = $score !== '' ? $score : 'N/A';
            @endphp

            <div class="result-box {{ $severityClass }}">

                <p><span class="value">Verdict:</span> {{ $verdict }}</p>
                <p><span class="value">Risk Score:</span> {{ $scoreDisplay }}</p>

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

                <p>{!! nl2br(e($customLegitAction ?? $recommended)) !!}</p>

            </div>

        {{-- Unexpected format --}}
        @else
            <div class="raw-output">
                <pre>{{ print_r($result, true) }}</pre>
            </div>
        @endif

    </div>

@endif

</body>
</html>
