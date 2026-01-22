{{-- resources/views/emails/sharpfleet/trial-ending.blade.php --}}
<x-sharpfleet-email-layout :title="'Trial ending soon'">
    <h1 style="margin:0 0 15px 0; font-size:22px; color:#0A2A4D; font-weight:600;">
        Trial ending soon
    </h1>

    @if($daysRemaining === 1)
        <p style="margin:0 0 18px 0; font-size:15px; color:#104976; line-height:1.6;">
            Dear {{ $name }}, your trial ends tomorrow. Subscribe to keep your vehicles, trips, and history active.
        </p>
    @else
        <p style="margin:0 0 18px 0; font-size:15px; color:#104976; line-height:1.6;">
            Dear {{ $name }}, your SharpFleet trial ends in {{ $daysRemaining }} days.
            You can continue uninterrupted by subscribing, it only takes a minute.
        </p>
    @endif

    <p style="text-align:center; margin:24px 0;">
        <a href="{{ $accountUrl }}"
           style="background:#2CBFAE; color:#ffffff !important; padding:12px 26px;
                  border-radius:8px; font-size:15px; font-weight:600; text-decoration:none;
                  display:inline-block;">
            Go to account
        </a>
    </p>

    <p style="margin:16px 0 0 0; font-size:14px; color:#6b7a89; line-height:1.5;">
        If you have any questions, reply to this email and we will help.
    </p>
</x-sharpfleet-email-layout>
