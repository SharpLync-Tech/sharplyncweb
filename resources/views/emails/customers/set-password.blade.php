<x-email-layout :title="'Set Your Password'">

    <h1 style="margin:0 0 15px 0; font-size:22px; color:#0A2A4D; font-weight:600;">
        Welcome to SharpLync 🎉
    </h1>

    <p style="margin:0 0 18px 0; color:#104976; font-size:15px; line-height:1.6;">
        Hi {{ $user->first_name ?? $user->name ?? '' }},  
        Your account has been created. You're almost ready to begin using the SharpLync
        Customer Portal.
    </p>

    <p style="margin:0 0 22px 0; color:#104976; font-size:15px;">
        Click the button below to set your password and complete your setup:
    </p>

    <p style="text-align:center; margin:30px 0;">
        <a href="{{ $setPasswordUrl }}"
           style="background:#104976; color:white !important; padding:14px 30px;
                  border-radius:8px; font-size:15px; font-weight:500; text-decoration:none;
                  display:inline-block;">
            Set My Password
        </a>
    </p>

    <p style="margin:22px 0 0 0; font-size:14px; color:#6b7a89; line-height:1.5;">
        If you didn’t expect this email, please ignore it.
    </p>

</x-email-layout>
