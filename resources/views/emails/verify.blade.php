{{-- resources/views/emails/verify.blade.php --}}
<x-email-layout :title="'Confirm Registration'">

    <h1 style="margin:0 0 15px 0; font-size:22px; color:#0A2A4D; font-weight:600;">
        Welcome, {{ $name ?? 'there' }}!
    </h1>

    <p style="margin:0 0 20px 0; font-size:15px; color:#104976; line-height:1.6;">
        Thanks for signing up with <strong>SharpLync</strong>.  
        We're excited to have you onboard and can't wait to support you!.
    </p>

    <p style="margin:0 0 25px 0; font-size:15px; color:#104976;">
        To complete your registration, please verify your email address by
        clicking the button below:
    </p>

    <!-- Button -->
    <p style="text-align:center; margin:30px 0;">
        <a href="{{ $verifyUrl }}"
           style="background:#104976; color:#ffffff !important; padding:14px 30px;
                  border-radius:8px; font-size:15px; font-weight:500; text-decoration:none;
                  display:inline-block;">
            Verify My Email
        </a>
    </p>

    <p style="margin:25px 0 0 0; font-size:14px; color:#6b7a89; line-height:1.5;">
        If you didn’t request this, you can safely ignore this email.  
        This verification link will expire for your security.
    </p>

</x-email-layout>
