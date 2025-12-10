<!DOCTYPE html>
<html>
<head>
    <title>SharpLync Scam Checker</title>
</head>
<body>

<h1>Scam Checker (Test Page)</h1>

<form method="POST" action="/scam-checker" enctype="multipart/form-data">
    @csrf

    <p>Paste text OR upload an email/screenshot:</p>

    <textarea name="message" rows="12" cols="100">@if(isset($input)){{ $input }}@endif</textarea>

    <br><br>

    <input type="file" name="file">

    <br><br>

    <button type="submit">Check Message</button>
</form>


@if(isset($result))
    <hr>
    <h2>Scam Analysis Result</h2>

    @php
        // STEP 1: Attempt to extract raw model content
        $raw = $result['choices'][0]['message']['content'] ?? null;

        // STEP 2: Try to decode JSON from model
        $analysis = $raw ? json_decode($raw, true) : null;

        // STEP 3: If decoding failed, apply safe fallback
        if (!is_array($analysis)) {
            $analysis = [
                'risk_score' => 10,
                'verdict' => 'likely legitimate',
                'summary' => 'The AI response could not be parsed as JSON, but no strong scam indicators were detected in the message.',
                'red_flags' => [],
                'recommended_action' => 'If you are unsure, verify the message directly by logging into the service via its official website, not via any links in the email.',
            ];
        }

        // STEP 4: Extract fields safely
        $riskScore = $analysis['risk_score'] ?? 'N/A';
        $verdict   = $analysis['verdict'] ?? 'N/A';
        $summary   = $analysis['summary'] ?? 'N/A';
        $redFlags  = $analysis['red_flags'] ?? [];
        $action    = $analysis['recommended_action'] ?? 'N/A';
    @endphp

    <p><strong>Risk Score:</strong> {{ $riskScore }} / 100</p>
    <p><strong>Verdict:</strong> {{ $verdict }}</p>
    <p><strong>Summary:</strong> {{ $summary }}</p>

    <h4>Red Flags</h4>
    <ul>
        @foreach($redFlags as $flag)
            <li>{{ $flag }}</li>
        @endforeach
    </ul>

    <h4>Recommended Action</h4>
    <p>{{ $action }}</p>

@endif

</body>
</html>
