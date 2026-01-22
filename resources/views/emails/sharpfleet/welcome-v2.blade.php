{{-- resources/views/emails/sharpfleet/welcome-v2.blade.php --}}
<x-sharpfleet-email-layout :title="'Welcome to SharpFleet'">
    <h1 style="margin:0 0 15px 0; font-size:22px; color:#0A2A4D; font-weight:600;">
        Welcome to SharpFleet
    </h1>

    <p style="margin:0 0 12px 0; font-size:15px; color:#104976; line-height:1.6;">
        Hi {{ $firstName ?? 'there' }},
    </p>

    <p style="margin:0 0 16px 0; font-size:15px; color:#104976; line-height:1.6;">
        Your account is active and your 30-day trial has started.
    </p>

    <p style="margin:0 0 14px 0; font-size:15px; color:#104976; line-height:1.6;">
        Here is what you can do to get started:
    </p>

    <ul style="margin:0 0 18px 18px; padding:0; color:#104976;">
        <li style="margin:0 0 8px 0;">Add vehicles and capture key details.</li>
        <li style="margin:0 0 8px 0;">Invite drivers and set their access.</li>
        <li style="margin:0 0 8px 0;">Start logging trips, servicing, and faults.</li>
    </ul>

    <p style="text-align:center; margin:24px 0;">
        <a href="{{ url('/app/sharpfleet/admin') }}"
           style="background:#2CBFAE; color:#ffffff !important; padding:12px 26px;
                  border-radius:8px; font-size:15px; font-weight:600; text-decoration:none;
                  display:inline-block;">
            Access your dashboard
        </a>
    </p>

    <p style="margin:16px 0 0 0; font-size:14px; color:#6b7a89; line-height:1.5;">
        Need help getting started? Use Feedback and Support in the app or email
        <a href="mailto:support@sharplync.com.au" style="color:#2CBFAE; text-decoration:none;">support@sharplync.com.au</a>.
    </p>
</x-sharpfleet-email-layout>
