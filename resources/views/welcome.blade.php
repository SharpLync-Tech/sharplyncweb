<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SharpLync - Coming Soon</title>
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
  <style>
    body {
      margin: 0;
      padding: 0;
      height: 100vh;
      background: linear-gradient(135deg, #0A2A4D 0%, #104976 45%, #2CBFAE 100%);
      font-family: 'Inter', 'Open Sans', system-ui, sans-serif;
      color: #ffffff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
    }

    img {
      height: 200px;
      width: auto;
      margin-bottom: 30px;
      filter: drop-shadow(0 0 8px rgba(0,0,0,0.5));
    }

    h1 {
      font-size: clamp(1.8rem, 4vw, 2.8rem);
      font-weight: 600;
      letter-spacing: 1px;
    }

    @media (max-width: 768px) {
      img {
        height: 180px;
      }
      h1 {
        font-size: clamp(1.5rem, 6vw, 2rem);
      }
    }
  </style>
</head>

<body>
  <img src="{{ asset('images/logo1.png') }}" alt="SharpLync Logo">
  <h1>Coming Soon</h1>
</body>
</html>