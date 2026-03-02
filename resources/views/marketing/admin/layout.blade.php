<!-- Marketing Page: Admin Layout -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SharpLync Marketing</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            margin:0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial;
            background:#f3f5f9;
        }

        .topbar {
            background:#0b1e3d;
            padding:18px 30px;
            color:white;
            font-size:18px;
            font-weight:600;
        }

        .container {
            max-width:1200px;
            margin:40px auto;
            padding:0 25px;
        }

        .card {
            background:white;
            border-radius:10px;
            padding:25px;
            box-shadow:0 4px 15px rgba(0,0,0,0.05);
            margin-bottom:30px;
        }

        .btn-primary {
            background:#0b1e3d;
            color:white;
            padding:10px 18px;
            border-radius:6px;
            text-decoration:none;
            display:inline-block;
        }

        .btn-send {
            background:#1f4fd8;
            color:white;
            border:none;
            padding:6px 14px;
            border-radius:4px;
            cursor:pointer;
        }

        table {
            width:100%;
            border-collapse:collapse;
        }

        th {
            text-align:left;
            font-size:13px;
            color:#666;
            padding:12px 10px;
            border-bottom:1px solid #eee;
        }

        td {
            padding:14px 10px;
            border-bottom:1px solid #f0f0f0;
        }

        .badge {
            padding:4px 10px;
            border-radius:20px;
            font-size:12px;
            font-weight:600;
        }

        .badge-draft {
            background:#fff4e5;
            color:#c77b00;
        }

        .badge-sent {
            background:#e6f7ef;
            color:#0f8a4b;
        }

        .stats {
            display:flex;
            gap:20px;
        }

        .stat-box {
            background:white;
            padding:20px;
            border-radius:10px;
            box-shadow:0 4px 15px rgba(0,0,0,0.05);
            flex:1;
        }

        .stat-number {
            font-size:28px;
            font-weight:700;
        }

    </style>
</head>
<body>

<div class="topbar">
    SharpLync Marketing Platform
</div>

<div class="container">
    @yield('content')
</div>

</body>
</html>