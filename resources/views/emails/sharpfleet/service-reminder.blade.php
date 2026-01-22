{{-- resources/views/emails/sharpfleet/service-reminder.blade.php --}}
<x-sharpfleet-email-layout :title="'SharpFleet servicing reminders'">

    <h1 style="margin:0 0 15px 0; font-size:22px; color:#0A2A4D; font-weight:600;">
        Servicing reminders
    </h1>

    <p style="margin:0 0 20px 0; font-size:15px; color:#104976; line-height:1.6;">
        Here is the latest servicing summary for <strong>{{ $organisationName ?? 'your organisation' }}</strong>.
    </p>

    @if (!empty($serviceDateOverdue) || !empty($serviceDateDueSoon))
        <h2 style="margin:20px 0 10px 0; font-size:16px; color:#0A2A4D; font-weight:600;">Due by date</h2>

        @if (!empty($serviceDateOverdue))
            <h3 style="margin:10px 0 8px 0; font-size:14px; color:#0A2A4D; font-weight:600;">Overdue</h3>
            <ul style="margin:0 0 15px 20px; padding:0; color:#104976;">
                @foreach ($serviceDateOverdue as $item)
                    <li style="margin:0 0 8px 0; font-size:14px; line-height:1.4;">
                        <strong>{{ $item['name'] ?? 'Vehicle' }}</strong>
                        — due {{ !empty($item['date']) ? \Carbon\Carbon::parse($item['date'])->format('d/m/Y') : 'unknown date' }}
                    </li>
                @endforeach
            </ul>
        @endif

        @if (!empty($serviceDateDueSoon))
            <h3 style="margin:10px 0 8px 0; font-size:14px; color:#0A2A4D; font-weight:600;">Due soon</h3>
            <ul style="margin:0 0 15px 20px; padding:0; color:#104976;">
                @foreach ($serviceDateDueSoon as $item)
                    <li style="margin:0 0 8px 0; font-size:14px; line-height:1.4;">
                        <strong>{{ $item['name'] ?? 'Vehicle' }}</strong>
                        — due {{ !empty($item['date']) ? \Carbon\Carbon::parse($item['date'])->format('d/m/Y') : 'unknown date' }}
                    </li>
                @endforeach
            </ul>
        @endif
    @endif

    @if (!empty($serviceReadingOverdue) || !empty($serviceReadingDueSoon))
        <h2 style="margin:20px 0 10px 0; font-size:16px; color:#0A2A4D; font-weight:600;">Due by reading</h2>

        @if (!empty($serviceReadingOverdue))
            <h3 style="margin:10px 0 8px 0; font-size:14px; color:#0A2A4D; font-weight:600;">Overdue</h3>
            <ul style="margin:0 0 15px 20px; padding:0; color:#104976;">
                @foreach ($serviceReadingOverdue as $item)
                    <li style="margin:0 0 8px 0; font-size:14px; line-height:1.4;">
                        <strong>{{ $item['name'] ?? 'Vehicle' }}</strong>
                        — due at {{ $item['due_reading'] ?? 'n/a' }}, last {{ $item['last_reading'] ?? 'n/a' }}
                    </li>
                @endforeach
            </ul>
        @endif

        @if (!empty($serviceReadingDueSoon))
            <h3 style="margin:10px 0 8px 0; font-size:14px; color:#0A2A4D; font-weight:600;">Due soon</h3>
            <ul style="margin:0 0 15px 20px; padding:0; color:#104976;">
                @foreach ($serviceReadingDueSoon as $item)
                    <li style="margin:0 0 8px 0; font-size:14px; line-height:1.4;">
                        <strong>{{ $item['name'] ?? 'Vehicle' }}</strong>
                        — due at {{ $item['due_reading'] ?? 'n/a' }}, last {{ $item['last_reading'] ?? 'n/a' }}
                    </li>
                @endforeach
            </ul>
        @endif
    @endif

    @if (empty($serviceDateOverdue) && empty($serviceDateDueSoon) && empty($serviceReadingOverdue) && empty($serviceReadingDueSoon))
        <p style="margin:0 0 20px 0; font-size:15px; color:#104976; line-height:1.6;">
            No servicing reminders at this time.
        </p>
    @endif

</x-sharpfleet-email-layout>
