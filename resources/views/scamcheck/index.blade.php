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
{{-- RAW OUTPUT DEBUG BLOCK (KEEP THIS FOR DEV) --}}
{{-- ========================================================= --}}
@if(isset($result))
    <div class="raw-output">
        <h3>Raw Output (Debug)</h3>
        <pre>{{ print_r($result, true) }}</pre>
    </div>
@endif



@if(isset($result))

    <div class="result-container">
        <h3>Scam Analysis Result</h3>

        {{-- Azure Error --}}
        @if(is_array($result) && isset($result['error']))
            <div class="raw-output">
                <strong>Azure Error:</strong>
                <pre>{{ print_r($result, true) }}</pre>
            </div>

        {{-- JSON FORMAT --}}
        @elseif(is_array($result) && isset($result['verdict']))

            @php
                $verdict = $result['verdict'] ?? 'Unknown';
                $score   = $result['risk_score'] ?? null;
                $summary = $result['summary'] ?? '';
                $redFlags = $result['red_flags'] ?? [];
                $recommended = $result['recommended_action'] ?? '';

                // Severity class
                if ($score !== null) {
                    $severityClass =
                        $score >= 70 ? 'danger' :
                        ($score >= 40 ? 'sus' : 'safe');
                } else {
                    $severityClass = 'safe';
                }

                // Score display
                $scoreDisplay = $score !== null ? $score : 'N/A';

                // Custom action if legit
                $customLegit = null;
                if (strtolower($verdict) === 'likely legitimate') {
                    $customLegit =
                        "We checked this email and found no signs of phishing.\n\n" .
                        "Still, stay cautious — if anything feels off, SharpLync can double-check it for you.";
                }
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
                            <p>- {{ $flag }}</p>
                        @endforeach
                    </div>
                @else
                    <p>No major red flags detected.</p>
                @endif

                <div class="section-title">Recommended Action</div>
                <p>{!! nl2br(e($customLegit ?? $recommended)) !!}</p>

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
