<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SMS Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            padding: 40px;
        }
        .container {
            background: #fff;
            padding: 25px 30px;
            width: 400px;
            border-radius: 12px;
            margin: auto;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        h2 { margin-top: 0; }
        input, textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        button {
            background: #0A2A4D;
            color: white;
            padding: 10px 15px;
            border: none;
            width: 100%;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        .alert {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }

        pre {
            padding: 15px;
            background: #f1f1f1;
            border-radius: 8px;
            overflow-x: auto;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>SMS API Sandbox</h2>
    <p>This page is NOT live. Safe for testing.</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ url('/test-sms/send') }}">
        @csrf

        <label>Phone Number</label>
        <input type="text" name="phone" placeholder="04XXXXXXXX" value="{{ old('phone') }}">

        <label>Message</label>
        <textarea name="message" rows="3" placeholder="Test message...">{{ old('message') }}</textarea>

        <button type="submit">Send SMS</button>
    </form>

    @if(session('response'))
        <h3>API Response</h3>
        <pre>{{ json_encode(session('response'), JSON_PRETTY_PRINT) }}</pre>
    @endif
</div>

</body>
</html>
