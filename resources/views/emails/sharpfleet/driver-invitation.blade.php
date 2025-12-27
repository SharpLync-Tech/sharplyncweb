<x-email-layout :title="'SharpFleet Invitation'">

    <h1 style="margin:0 0 15px 0; font-size:22px; color:#0A2A4D; font-weight:600;">
        You\'re invited to SharpFleet
    </h1>

    <p style="margin:0 0 20px 0; font-size:15px; color:#104976; line-height:1.6;">
        You\'ve been invited to join
        <strong>{{ $organisationName ?? 'an organisation' }}</strong>
        as a driver in <strong>SharpFleet</strong>.
    </p>

    <p style="margin:0 0 25px 0; font-size:15px; color:#104976;">
        Click the button below to set your name and password and activate your driver account.
    </p>

    <p style="text-align:center; margin:30px 0;">
        <a href="{{ $acceptUrl }}"
           style="background:#104976; color:#ffffff !important; padding:14px 30px;
                  border-radius:8px; font-size:15px; font-weight:500; text-decoration:none;
                  display:inline-block;">
            Accept Invitation
        </a>
    </p>

    <p style="margin:25px 0 0 0; font-size:14px; color:#6b7a89; line-height:1.5;">
        This invitation link expires for your security. If you weren\'t expecting this email, you can safely ignore it.
    </p>

</x-email-layout>
