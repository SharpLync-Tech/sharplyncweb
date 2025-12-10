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
        $json = json_decode($result['choices'][0]['message']['content'] ?? '{}', true);
    @endphp

    @if(json_last_error() === JSON_ERROR_NONE)
        <p><strong>Risk Score:</strong> {{ $json['risk_score'] ?? 'N/A' }} / 100</p>
        <p><strong>Verdict:</strong> {{ $json['verdict'] ?? 'N/A' }}</p>
        <p><strong>Summary:</strong> {{ $json['summary'] ?? 'N/A' }}</p>

        <h4>Red Flags</h4>
        <ul>
            @foreach(($json['red_flags'] ?? []) as $flag)
                <li>{{ $flag }}</li>
            @endforeach
        </ul>

        <h4>Recommended Action</h4>
        <p>{{ $json['recommendation'] ?? 'N/A' }}</p>
    @else
        <p><strong>AI returned invalid JSON:</strong></p>
        <pre>{{ print_r($result, true) }}</pre>
    @endif

@endif


</body>
</html>
