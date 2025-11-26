<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'SharpLync Notification' }}</title>
</head>
<body style="margin:0; padding:0; background:#f4f7fb; font-family:Arial, sans-serif; color:#0A2A4D;">

<table width="100%" cellpadding="0" cellspacing="0" style="margin:0; padding:2rem 0;">
    <tr>
        <td align="center">

            <!-- Outer container -->
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 8px 20px rgba(10,42,77,0.12);">

                <!-- Header -->
                <tr>
                    <td style="padding:24px 24px; background:#0A2A4D;" align="center">
                        <img src="{{ asset('images/sharplync-logo.png') }}"
                             alt="SharpLync"
                             style="max-width:220px; display:block; margin-bottom:8px;">
                        <p style="margin:0; font-size:13px; color:#D7E2F2; letter-spacing:0.05em;">
                            Your Personal Tech Link — Backed by Experience
                        </p>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:28px 24px;">
                        @yield('content')
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:18px 24px 24px 24px; background:#f4f7fb; font-size:12px; color:#6b7a90;">
                        <p style="margin:0 0 4px 0;">
                            &copy; {{ date('Y') }} SharpLync Pty Ltd. All rights reserved.
                        </p>
                        <p style="margin:0;">
                            This email was sent from the SharpLync website contact form.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
