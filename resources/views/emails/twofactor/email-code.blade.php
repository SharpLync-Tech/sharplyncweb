<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; background:#f4f7fb; padding:40px;">
    <div style="max-width:600px; margin:auto; background:#ffffff; padding:30px; border-radius:12px;">
        
        <h2 style="color:#0A2A4D; margin-top:0;">Your SharpLync Security Code</h2>

        <p>Hello {{ $user->first_name }},</p>

        <p>Use the verification code below to continue setting up Email Authentication:</p>

        <div style="background:#0A2A4D; color:#fff; font-size:28px; font-weight:bold; padding:15px 25px; border-radius:10px; text-align:center; letter-spacing:4px;">
            {{ $code }}
        </div>

        <p style="margin-top:25px;">
            This code expires in <strong>10 minutes</strong>.
        </p>

        <p>If you did not request this, simply ignore this message.</p>

        <p style="margin-top:40px;">Kind regards,<br>
        <strong>SharpLync Security</strong></p>
    </div>
</body>
</html>
