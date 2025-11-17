{{-- resources/views/emails/verify.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verify your SharpLync account</title>
  <style>
    body {
      font-family: 'Poppins', Arial, sans-serif;
      background: #e9eef2;
      color: #0A2A4D;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 620px;
      margin: 40px auto;
      background: #ffffff;
      border-radius: 12px;
      padding: 35px 35px 40px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .header {
      text-align: center;
      padding-bottom: 25px;
      border-bottom: 1px solid #f0f0f0;
      margin-bottom: 30px;
    }

    .logo img {
      width: 100px;
      height: auto;
    }

    h1 {
      color: #0A2A4D;
      font-size: 24px;
      margin-bottom: 10px;
    }

    p {
      line-height: 1.65;
      font-size: 15px;
      margin: 12px 0;
      color: #104976;
    }

    .btn {
      display: inline-block;
      background: #104976;
      color: #ffffff !important;
      padding: 14px 30px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 15px;
      font-weight: 500;
      margin-top: 20px;
      transition: opacity .2s;
    }

    .btn:hover {
      opacity: 0.9;
    }

    .footer {
      margin-top: 45px;
      font-size: 13px;
      color: #7a8895;
      text-align: center;
      border-top: 1px solid #f0f0f0;
      padding-top: 20px;
    }
  </style>
</head>
<body>
  <div class="container">

    <div class="header">
      <div class="logo">
        <img src="https://sharplync.com.au/images/sharplync-logo.png" alt="SharpLync Logo">
      </div>
    </div>

    <h1>Welcome, {{ $name }}!</h1>

    <p>Thanks for signing up with <strong>SharpLync</strong>. We're excited to have you onboard.</p>

    <p>To complete your registration, please verify your email address by clicking the button below:</p>

    <p style="text-align: center;">
      <a href="{{ $verifyUrl }}" class="btn">Verify My Email</a>
    </p>

    <p>If you didn’t request this, you can safely ignore this message.</p>

    <div class="footer">
      &copy; {{ date('Y') }} SharpLync Pty Ltd — All rights reserved.
    </div>
  </div>
</body>
</html>