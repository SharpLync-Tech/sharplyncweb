{{-- resources/views/emails/sharpfleet/rego-reminder.blade.php --}}
<x-sharpfleet-email-layout :title="'SharpFleet registration reminders'">

    <h1 style="margin:0 0 15px 0; font-size:22px; color:#0A2A4D; font-weight:600;">
        Registration reminders
    </h1>

    <p style="margin:0 0 20px 0; font-size:15px; color:#104976; line-height:1.6;">
        Here is the latest vehicle registration summary for <strong>{{ $organisationName ?? 'your organisation' }}</strong>.
    </p>

    @if (!empty($overdue))
        <h2 style="margin:20px 0 10px 0; font-size:16px; color:#0A2A4D; font-weight:600;">Overdue</h2>
        <ul style="margin:0 0 15px 20px; padding:0; color:#104976;">
            @foreach ($overdue as $item)
                <li style="margin:0 0 8px 0; font-size:14px; line-height:1.4;">
                    <strong>{{ $item['name'] ?? 'Vehicle' }}</strong>
                    @if (!empty($item['registration_number']))
                        ({{ $item['registration_number'] }})
                    @endif
                    — expired {{ !empty($item['date']) ? \Carbon\Carbon::parse($item['date'])->format('d/m/Y') : 'unknown date' }}
                </li>
            @endforeach
        </ul>
    @endif

    @if (!empty($dueSoon))
        <h2 style="margin:20px 0 10px 0; font-size:16px; color:#0A2A4D; font-weight:600;">Due soon</h2>
        <ul style="margin:0 0 15px 20px; padding:0; color:#104976;">
            @foreach ($dueSoon as $item)
                <li style="margin:0 0 8px 0; font-size:14px; line-height:1.4;">
                    <strong>{{ $item['name'] ?? 'Vehicle' }}</strong>
                    @if (!empty($item['registration_number']))
                        ({{ $item['registration_number'] }})
                    @endif
                    — due {{ !empty($item['date']) ? \Carbon\Carbon::parse($item['date'])->format('d/m/Y') : 'unknown date' }}
                </li>
            @endforeach
        </ul>
    @endif

    @if (empty($overdue) && empty($dueSoon))
        <p style="margin:0 0 20px 0; font-size:15px; color:#104976; line-height:1.6;">
            No registration reminders at this time.
        </p>
    @endif

</x-sharpfleet-email-layout>
