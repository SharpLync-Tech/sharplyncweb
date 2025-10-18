<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SharpLync — Coming Soon</title>

    {{-- Global SharpLync Stylesheet --}}
    <link rel="stylesheet" href="{{ secure_asset('css/app.css') }}">

    {{-- Inline brand-specific background (not in app.css) --}}
    <style>
        body {
            background: radial-gradient(circle at center, #104946 0%, #0A2A4D 100%);
            color: #f4f4f4;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .welcome-container {
            text-align: center;
            animation: fadeIn 1.5s ease-in-out;
        }
        .welcome-logo {
            width: 300px;
            height: auto;
            margin-bottom: 30px;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));
        }
        .welcome-text {
            font-size: 1.2rem;
            opacity: 0.9;
            margin: 8px 0;
        }
        .welcome-footer {
            position: absolute;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 0.9rem;
            opacity: 0.7;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body>
    <div class="welcome-container">
        <img src="{{ asset('images/logo.png') }}" alt="SharpLync Logo" class="welcome-logo">
        <p class="welcome-text">Your Personal Tech Link — Backed by Experience.</p>
        <p class="welcome-text"><em>Website coming soon...</em></p>
    </div>

    <div class="welcome-footer">
        &copy; {{ date('Y') }} SharpLync. All rights reserved.
    </div>
</body>
</html>
