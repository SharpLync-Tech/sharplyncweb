<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SharpLync Marketing Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            margin:0;
            font-family: Arial, Helvetica, sans-serif;
            background:#f4f6f8;
        }

        .nav {
            background:#0b1e3d;
            padding:16px 30px;
            color:white;
            font-size:18px;
            font-weight:600;
        }

        .container {
            max-width:1100px;
            margin:40px auto;
            padding:0 20px;
        }

        .btn-primary {
            background:#0b1e3d;
            color:white;
            padding:10px 18px;
            border-radius:6px;
            text-decoration:none;
            display:inline-block;
        }

        .btn-action {
            background:#1f4fd8;
            color:white;
            border:none;
            padding:6px 12px;
            border-radius:4px;
            cursor:pointer;
        }

        table {
            width:100%;
            border-collapse:collapse;
        }

        th {
            background:#0b1e3d;
            color:white;
            text-align:left;
            padding:10px;
        }

        td {
            padding:10px;
            border-bottom:1px solid #ddd;
        }

        .alert-success {
            background:#e6f7ef;
            padding:12px;
            border-radius:6px;
            margin-bottom:20px;
        }
    </style>
</head>
<body>

<div class="nav">
    SharpLync Marketing Admin
</div>

<div class="container">
    @yield('content')
</div>

</body>
</html>