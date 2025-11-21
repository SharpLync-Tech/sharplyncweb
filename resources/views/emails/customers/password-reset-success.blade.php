<x-email-layout :title="'Your Password Has Been Reset'">

    <h1 style="margin:0 0 15px 0; font-size:22px; color:#0A2A4D; font-weight:600;">
        Password Updated Successfully
    </h1>

    <p style="margin:0 0 18px 0; color:#104976; font-size:15px; line-height:1.6;">
        Hi {{ $user->first_name ?? $user->name ?? '' }},  
        This is a confirmation that your SharpLync Customer Portal password
        was successfully changed.
    </p>

    <p style="margin:0 0 22px 0; color:#104976; font-size:15px;">
        If this wasn’t you, please reset your password immediately or contact us.
    </p>

    <p style="text-align:center; margin:30px 0;">
        <a href="{{ url('/login') }}"
           style="background:#104976; color:white !important; padding:14px 30px;
                  border-radius:8px; font-size:15px; font-weight:500; text-decoration:none;
                  display:inline-block;">
            Log in to Your Account
        </a>
    </p>

</x-email-layout>
