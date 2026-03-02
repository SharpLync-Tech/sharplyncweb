<!-- Marketing Email: Confirm Subscription -->
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Confirm Subscription</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f4f6f8; padding:30px;">

<table width="600" align="center" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; padding:30px;">
<tr>
<td>

<h2>Please Confirm Your Subscription</h2>

<p>
Click the button below to confirm your subscription.
</p>

<p style="text-align:center; margin:30px 0;">
<a href="{{ url('/marketing/confirm/'.$subscriber->confirmation_token) }}"
   style="background:#0ea5e9; color:#ffffff; padding:12px 24px; text-decoration:none; border-radius:6px;">
   Confirm Subscription
</a>
</p>

<p style="font-size:12px; color:#666;">
If you did not request this, you can ignore this email.
</p>

</td>
</tr>
</table>

</body>
</html>