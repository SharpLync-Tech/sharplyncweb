<!-- Marketing Email: Master Layout -->
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $subject ?? 'SharpLync Update' }}</title>
</head>

<body style="margin:0; padding:0; background:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">

@if(!empty($preheader))
<div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">
    {{ $preheader }}
</div>
@endif

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8; padding:40px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden;">

    <!-- HEADER -->
    <tr>
        <td style="background:#0b1f3a; padding:18px 20px; text-align:center;">
            @if($brand === 'sf')
                <h1 style="color:#ffffff; margin:0; font-size:20px; font-weight:600;">
                    SharpFleet
                </h1>
            @else
                <img src="https://sharplync.com.au/images/sharplync-logo.png"
                     alt="SharpLync Logo"
                     style="max-width:180px; height:auto; display:block; margin:0 auto;">
            @endif
        </td>
    </tr>

    <!-- ACCENT LINE -->
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

            @if(!empty($unsubscribeUrl))
                <p style="margin:0 0 10px 0;">
                    If you no longer wish to receive these emails, you can
                    <a href="{{ $unsubscribeUrl }}" style="color:#0ea5e9; text-decoration:none;">unsubscribe here</a>.
                </p>
            @endif

            @if(!empty($preferencesUrl))
                <p style="margin:0 0 10px 0;">
                    <a href="{{ $preferencesUrl }}" style="color:#0ea5e9; text-decoration:none;">
                        Manage preferences
                    </a>
                </p>
            @endif

            <!-- Footer legal moved into brand templates -->

        </td>
    </tr>

</table>

</td>
</tr>
</table>

</body>
</html>
