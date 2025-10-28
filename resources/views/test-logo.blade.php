@extends('layouts.app')

@section('title', 'SharpLync Test Logo')

@section('content')

  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background-color: #f2f2f2;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .container {
      display: flex;
      justify-content: center;
      align-items: stretch;
      gap: 20px;
    }

    .tile {
      background-color: white;
      border: 1px solid #ccc;
      width: 200px;
      height: 150px;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 1.2rem;
      font-weight: bold;
      color: #0A2A4D;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="tile">Tile 1</div>
    <div class="tile">Tile 2</div>
    <div class="tile">Tile 3</div>
    <div class="tile">Tile 4</div>
  </div>
</body>
</html>
@endsection