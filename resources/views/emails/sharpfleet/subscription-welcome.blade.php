{{-- resources/views/emails/sharpfleet/subscription-welcome.blade.php --}}
<x-sharpfleet-email-layout :title="'Welcome to SharpFleet'">
    <h1 style="margin:0 0 15px 0; font-size:22px; color:#0A2A4D; font-weight:600;">
        Welcome to SharpFleet
    </h1>

    <p style="margin:0 0 16px 0; font-size:15px; color:#104976; line-height:1.6;">
        Hi {{ $firstName ?? 'there' }},
    </p>

    <p style="margin:0 0 16px 0; font-size:15px; color:#104976; line-height:1.6;">
        Welcome to SharpFleet, and thanks for subscribing! 👋
        Your subscription is now active, and you’re good to go.
    </p>

    <h2 style="margin:18px 0 10px 0; font-size:16px; color:#0A2A4D; font-weight:600;">
        What this means
    </h2>
    <ul style="margin:0 0 16px 18px; padding:0; color:#104976;">
        <li style="margin:0 0 8px 0;">Your vehicles and trips will continue uninterrupted</li>
        <li style="margin:0 0 8px 0;">All features are fully available</li>
        <li style="margin:0 0 8px 0;">Your data stays safe, accurate, and accessible</li>
    </ul>

    <p style="margin:0 0 16px 0; font-size:15px; color:#104976; line-height:1.6;">
        There’s nothing else you need to do right now, just keep using SharpFleet as normal.
    </p>

    <h2 style="margin:18px 0 10px 0; font-size:16px; color:#0A2A4D; font-weight:600;">
        Getting the most out of SharpFleet
    </h2>
    <p style="margin:0 0 10px 0; font-size:15px; color:#104976; line-height:1.6;">
        If you haven’t already, a great next step is to:
    </p>
    <ul style="margin:0 0 16px 18px; padding:0; color:#104976;">
        <li style="margin:0 0 8px 0;"><span style="color:#2CBFAE; font-weight:700;">•</span> Add your vehicles</li>
        <li style="margin:0 0 8px 0;"><span style="color:#2CBFAE; font-weight:700;">•</span> Invite drivers</li>
        <li style="margin:0 0 8px 0;"><span style="color:#2CBFAE; font-weight:700;">•</span> Start your first trip (or a few 😉)</li>
    </ul>

    <p style="margin:0 0 16px 0; font-size:15px; color:#104976; line-height:1.6;">
        SharpFleet is designed to stay out of your way and just work, whether you’re managing one vehicle or a whole fleet.
    </p>

    <h2 style="margin:18px 0 10px 0; font-size:16px; color:#0A2A4D; font-weight:600;">
        Need help?
    </h2>
    <p style="margin:0 0 16px 0; font-size:15px; color:#104976; line-height:1.6;">
        If you have questions, want help setting things up, or just want to sanity-check you’re doing things the best way for your business, we’re here to help.
    </p>
    <p style="margin:0 0 16px 0; font-size:15px; color:#104976; line-height:1.6;">
        You can reach us anytime via the Feedback & Support section in the app.
    </p>

    <p style="margin:0; font-size:15px; color:#104976; line-height:1.6;">
        Thanks again for choosing SharpFleet, we really appreciate it.
    </p>

    <p style="margin:12px 0 0 0; font-size:15px; color:#104976; line-height:1.6;">
        Cheers,<br>
        The SharpFleet Team
    </p>
</x-sharpfleet-email-layout>
