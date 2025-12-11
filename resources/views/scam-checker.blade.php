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

    {{-- If Azure returned an error --}}
    @if(is_array($result) && isset($result['error']))
        <pre>{{ print_r($result, true) }}</pre>

    {{-- If the model returned plain structured text --}}
    @elseif(is_string($result))
        <pre>{{ $result }}</pre>

    {{-- If something unexpected happened --}}
    @else
        <pre>{{ print_r($result, true) }}</pre>
    @endif

@endif

</body>
</html>
