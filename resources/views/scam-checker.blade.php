<!DOCTYPE html>
<html>
<head>
    <title>SharpLync Scam Checker</title>
</head>
<body>

<h1>Scam Checker (Test Page)</h1>

<form method="POST" action="/scam-checker">
    @csrf
    <textarea name="message" rows="12" cols="100" placeholder="Paste scam email or message here">@if(isset($input)){{ $input }}@endif</textarea>
    <br><br>
    <button type="submit">Check Message</button>
</form>

@if(isset($result))
    <hr>
    <h2>AI Result</h2>
    <pre>{{ print_r($result, true) }}</pre>
@endif

</body>
</html>
