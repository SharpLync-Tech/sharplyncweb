<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $subject ?? 'SharpLync Update' }}</title>
</head>

<body style="margin:0; padding:0; background:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8; padding:40px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden;">

    <!-- NAVY HEADER -->
    <tr>
        <td style="background:#0b1f3a; padding:28px 30px; text-align:left;">
            <h1 style="color:#ffffff; margin:0; font-size:20px; font-weight:600;">
                {{ $brand === 'sf' ? 'SharpFleet' : 'SharpLync' }}
            </h1>
        </td>
    </tr>

    <!-- TEAL ACCENT LINE -->
    <tr>
        <td style="height:4px; background:#0ea5e9;"></td>
    </tr>

    <!-- OPTIONAL HERO IMAGE -->
    @if(!empty($heroImage))
    <tr>
        <td>
            <img src="{{ $heroImage }}" width="600" style="display:block; width:100%; height:auto;">
        </td>
    </tr>
    @endif

    <!-- CONTENT -->
    <tr>
        <td style="padding:40px 30px; color:#333333; font-size:15px; line-height:1.6;">
            @yield('content')
        </td>
    </tr>

    <!-- FOOTER -->
    <tr>
        <td style="background:#f1f5f9; padding:20px 30px; font-size:12px; color:#666; line-height:1.5;">

            <p style="margin:0 0 10px 0;">
                You are receiving this email because you subscribed to {{ $brand === 'sf' ? 'SharpFleet' : 'SharpLync' }} updates.
            </p>

            @if(!empty($unsubscribeUrl))
                <p style="margin:0;">
                    <a href="{{ $unsubscribeUrl }}" style="color:#0ea5e9; text-decoration:none;">
                        Unsubscribe
                    </a>
                </p>
            @endif

            <p style="margin:15px 0 0 0;">
                © {{ date('Y') }} SharpLync Pty Ltd
            </p>

        </td>
    </tr>

</table>

</td>
</tr>
</table>

</body>
</html>