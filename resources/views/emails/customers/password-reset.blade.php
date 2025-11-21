<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset your SharpLync password</title>
</head>
<body style="font-family:Arial, sans-serif; background:#f5f7fb; padding:20px;">

    <div style="max-width:600px; margin:0 auto; background:#ffffff;
                padding:20px; border-radius:8px;">

        <h2 style="color:#0A2A4D; margin-top:0;">Reset Your Password</h2>

        <p>Hello {{ $user->first_name ?? 'there' }},</p>

        <p>We received a request to reset the password for your SharpLync Portal account.</p>

        <p style="text-align:center; margin:30px 0;">
            <a href="{{ $resetUrl }}"
               style="background:#2CBFAE; color:white; padding:12px 20px;
                      text-decoration:none; border-radius:6px; font-weight:bold;">
                Reset my password
            </a>
        </p>

        <p>If the button above doesn't work, copy and paste this URL into your browser:</p>

        <p style="word-break:break-all; color:#333; font-size:0.9rem;">
            {{ $resetUrl }}
        </p>

        <p>This link will expire in 60 minutes.</p>

        <p style="margin-top:30px;">Regards,<br>SharpLync Support</p>
    </div>

</body>
</html>
