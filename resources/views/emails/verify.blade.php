{{-- resources/views/emails/verify.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verify your SharpLync account</title>
  <style>
    body { font-family: 'Poppins', Arial, sans-serif; background: #f4f6f8; color: #0A2A4D; }
    .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h1 { color: #104946; font-size: 22px; margin-bottom: 15px; }
    p { line-height: 1.6; }
    .btn {
      display: inline-block;
      background-color: #104946;
      color: #fff !important;
      padding: 12px 25px;
      border-radius: 6px;
      text-decoration: none;
      margin-top: 20px;
    }
    .footer { margin-top: 40px; font-size: 13px; color: #888; text-align: center; }
    .logo { text-align: center; margin-bottom: 25px; }
    .logo img { width: 90px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="logo">
      <img src="https://sharplync.com.au/images/sharplync-logo.png" alt="SharpLync Logo">
    </div>

    <h1>Welcome, {{ $name }}!</h1>
    <p>Thanks for signing up with <strong>SharpLync</strong>.</p>
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