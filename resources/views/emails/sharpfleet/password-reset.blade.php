{{-- resources/views/emails/sharpfleet/password-reset.blade.php --}}
<x-sharpfleet-email-layout :title="'Reset your SharpFleet password'">

    <h1 style="margin:0 0 15px 0; font-size:22px; color:#0A2A4D; font-weight:600;">
        Hi {{ $name ?? 'there' }},
    </h1>

    <p style="margin:0 0 20px 0; font-size:15px; color:#104976; line-height:1.6;">
        We received a request to reset the password for your <strong>SharpFleet</strong> account.
    </p>

    <p style="margin:0 0 25px 0; font-size:15px; color:#104976;">
        Click the button below to choose a new password:
    </p>

    <p style="text-align:center; margin:30px 0;">
        <a href="{{ $resetUrl }}"
           style="background:#104976; color:#ffffff !important; padding:14px 30px;
                  border-radius:8px; font-size:15px; font-weight:500; text-decoration:none;
                  display:inline-block;">
            Reset My Password
        </a>
    </p>

    <p style="margin:25px 0 0 0; font-size:14px; color:#6b7a89; line-height:1.5;">
        This link will expire in {{ (int) ($expiresMinutes ?? 30) }} minutes.
        If you didn’t request a password reset, you can safely ignore this email.
    </p>

</x-sharpfleet-email-layout>
