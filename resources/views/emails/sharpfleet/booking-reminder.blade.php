<x-sharpfleet-email-layout :title="'Booking reminder'">
    <h2 style="margin:0 0 12px 0;">Booking reminder</h2>

    <p style="margin:0 0 12px 0;">
        Hi {{ $driverName }},
    </p>

    <p style="margin:0 0 12px 0;">
        This is a reminder that you have a vehicle booking starting in about an hour.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="padding:6px 0; width:180px;"><strong>Start</strong></td>
            <td style="padding:6px 0;">{{ $start->format('d/m/Y H:i') }} ({{ $timezone }})</td>
        </tr>
        <tr>
            <td style="padding:6px 0;"><strong>End</strong></td>
            <td style="padding:6px 0;">{{ $end->format('d/m/Y H:i') }} ({{ $timezone }})</td>
        </tr>
        <tr>
            <td style="padding:6px 0;"><strong>Vehicle</strong></td>
            <td style="padding:6px 0;">
                {{ $vehicleName ?: '—' }}
                @if(trim((string) $vehicleReg) !== '')
                    ({{ $vehicleReg }})
                @endif
            </td>
        </tr>
        @if(trim((string) $customerName) !== '')
            <tr>
                <td style="padding:6px 0;"><strong>Customer / Client</strong></td>
                <td style="padding:6px 0;">{{ $customerName }}</td>
            </tr>
        @endif
        @if(trim((string) $notes) !== '')
            <tr>
                <td style="padding:6px 0;"><strong>Notes</strong></td>
                <td style="padding:6px 0;">{{ $notes }}</td>
            </tr>
        @endif
    </table>

    <p style="margin:16px 0 0 0;" class="text-muted">
        You received this reminder because “Reminder (1 hour before start)” was selected when the booking was created or edited.
    </p>
</x-sharpfleet-email-layout>
