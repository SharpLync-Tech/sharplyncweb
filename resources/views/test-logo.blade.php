<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SharpLync | Test Logo</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f2f2f2;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    header {
      background-color: #0A2A4D;
      color: white;
      text-align: center;
      padding: 1rem 0;
      font-size: 1.5rem;
      font-weight: bold;
    }

    footer {
      background-color: #0A2A4D;
      color: white;
      text-align: center;
      padding: 0.8rem 0;
      font-size: 0.9rem;
      margin-top: auto;
    }

    .container {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 20px;
      padding: 2rem 0;
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
  <header>
    SharpLync
  </header>

  <div class="container">
    <div class="tile">Tile 1</div>
    <div class="tile">Tile 2</div>
    <div class="tile">Tile 3</div>
    <div class="tile">Tile 4</div>
  </div>

  <footer>
    &copy; <?php echo date('Y'); ?> SharpLync Pty Ltd. All rights reserved.
  </footer>
</body>
</html>
