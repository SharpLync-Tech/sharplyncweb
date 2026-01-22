<!-- resources/views/emails/layouts/sharpfleet.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'SharpFleet Notification' }}</title>
</head>

<body style="margin:0; padding:0; background:#f4f7fb; font-family:Arial, sans-serif; color:#0A2A4D; font-size:15px; line-height:1.6;">

<table width="100%" cellpadding="0" cellspacing="0" style="margin:0; padding:2rem 0;">
    <tr>
        <td align="center">

            <!-- Outer container -->
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden;">

                <!-- Header -->
                <tr>
                    <td style="padding:28px 20px; background:#0A2A4D;" align="center">
                        <img src="{{ asset('images/sharpfleet/logo.png') }}"
                             alt="SharpFleet Logo"
                             style="max-width:200px; margin-bottom:6px;">
                        <p style="margin:0; color:#2CBFAE; font-size:12px; letter-spacing:1px;">
                            FLEET MANAGEMENT, SHARP BY DESIGN
                        </p>
                    </td>
                </tr>

                <!-- Main Content Area -->
                <tr>
                    <td style="padding:30px;">
                        {{ $slot }}
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:18px 20px; text-align:center; font-size:12px; color:#6b7a89; border-top:1px solid #e0e7ef;">
                        SharpFleet is <a href="https://sharplync.com.au" style="color:#2CBFAE; text-decoration:none;">SharpLync</a> Product.
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
