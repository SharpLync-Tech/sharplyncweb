{{-- resources/views/emails/welcome.blade.php --}}
<x-email-layout :title="'Welcome to SharpLync'">

    <h1 style="margin:0 0 15px 0; font-size:22px; color:#0A2A4D; font-weight:600;">
        Welcome to SharpLync!
    </h1>

    <p style="margin:0 0 18px 0; font-size:15px; color:#104976; line-height:1.6;">
        Your account has been successfully created — we're thrilled to have you onboard.
    </p>

    <p style="margin:0 0 20px 0; font-size:15px; color:#104976;">
        You can now log in anytime and access your personalised customer portal.
    </p>

    <!-- Next Steps -->
    <div style="background:#e7f7f5; border-left:4px solid #2CBFAE; padding:15px 18px;
                border-radius:6px; margin:25px 0;">
        <p style="margin:0; font-size:15px; color:#0A2A4D; line-height:1.6;">
            <strong>Next steps:</strong><br>
            → Log in<br>
            → Complete your profile<br>
            → Add your devices<br>
            → Explore your customer portal
        </p>
    </div>

    <!-- Login Button -->
    <p style="text-align:center; margin:30px 0;">
        <a href="{{ url('/login') }}"
           style="background:#104976; color:#ffffff !important; padding:14px 28px;
                  border-radius:8px; font-size:15px; font-weight:500; text-decoration:none;
                  display:inline-block;">
            Log In to Your Portal
        </a>
    </p>

    <p style="margin:25px 0 0 0; font-size:14px; color:#6b7a89; line-height:1.5;">
        If you need any help getting started, reply to this email or contact us anytime.
        We're here to help.
    </p>

</x-email-layout>
