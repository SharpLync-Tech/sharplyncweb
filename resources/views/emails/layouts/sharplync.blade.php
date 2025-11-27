<!-- resources/views/emails/layouts/sharplync.blade.php -->

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
            <table width="600" cellpadding="0" cellspacing="0" style="background:white; border-radius:12px; overflow:hidden;">

                <!-- Header -->
                <tr>
                    <td style="padding:30px 20px; background:#0A2A4D;" align="center">

                        <img src="{{ asset('storage/uploads/webemail.png') }}" 
                             alt="SharpLync" 
                             style="max-width:220px; margin-bottom:10px;">

                        <p style="margin:0; color:#2CBFAE; font-size:13px; letter-spacing:1px;">
                            YOUR PERSONAL TECH LINK, BACKED BY EXPERIENCE
                        </p>
                    </td>
                </tr>

                <!-- Main Content Area -->
                <tr>
                    <td style="padding:30px;">

                        @yield('content')

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:20px; text-align:center; font-size:12px; color:#6b7a89; border-top:1px solid #e0e7ef;">
                        © {{ date('Y') }} SharpLync Pty Ltd — All rights reserved.
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
