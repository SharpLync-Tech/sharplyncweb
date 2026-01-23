{{-- resources/views/emails/sharpfleet/fault-reported.blade.php --}}
<x-sharpfleet-email-layout :title="'SharpFleet issue/accident report'">

    <h1 style="margin:0 0 15px 0; font-size:22px; color:#0A2A4D; font-weight:600;">
        {{ ucfirst($reportType ?? 'issue') }} reported
    </h1>

    <p style="margin:0 0 20px 0; font-size:15px; color:#104976; line-height:1.6;">
        A driver reported a {{ $reportType ?? 'issue' }} for <strong>{{ $organisationName ?? 'your organisation' }}</strong>.
    </p>

    <table style="width:100%; border-collapse:collapse; margin:0 0 20px 0;">
        <tr>
            <td style="padding:6px 0; font-size:14px; color:#0A2A4D; font-weight:600; width:160px;">Vehicle</td>
            <td style="padding:6px 0; font-size:14px; color:#104976;">
                {{ $vehicleName ?? 'Vehicle' }}
                @if(!empty($vehicleRegistration))
                    ({{ $vehicleRegistration }})
                @endif
            </td>
        </tr>
        <tr>
            <td style="padding:6px 0; font-size:14px; color:#0A2A4D; font-weight:600;">Severity</td>
            <td style="padding:6px 0; font-size:14px; color:#104976;">{{ ucfirst($severity ?? 'minor') }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; font-size:14px; color:#0A2A4D; font-weight:600;">Reported by</td>
            <td style="padding:6px 0; font-size:14px; color:#104976;">
                {{ $reporterName ?? 'Driver' }}
                @if(!empty($reporterEmail))
                    ({{ $reporterEmail }})
                @endif
            </td>
        </tr>
        @if(!empty($occurredAt))
            <tr>
                <td style="padding:6px 0; font-size:14px; color:#0A2A4D; font-weight:600;">Occurred at</td>
                <td style="padding:6px 0; font-size:14px; color:#104976;">{{ $occurredAt }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding:6px 0; font-size:14px; color:#0A2A4D; font-weight:600;">Reported at</td>
            <td style="padding:6px 0; font-size:14px; color:#104976;">{{ $reportedAt ?? '' }}</td>
        </tr>
        @if(!empty($tripId))
            <tr>
                <td style="padding:6px 0; font-size:14px; color:#0A2A4D; font-weight:600;">Trip ID</td>
                <td style="padding:6px 0; font-size:14px; color:#104976;">{{ $tripId }}</td>
            </tr>
        @endif
    </table>

    @if(!empty($title))
        <h2 style="margin:0 0 8px 0; font-size:16px; color:#0A2A4D; font-weight:600;">Title</h2>
        <p style="margin:0 0 16px 0; font-size:14px; color:#104976; line-height:1.6;">{{ $title }}</p>
    @endif

    <h2 style="margin:0 0 8px 0; font-size:16px; color:#0A2A4D; font-weight:600;">Description</h2>
    <p style="margin:0 0 20px 0; font-size:14px; color:#104976; line-height:1.6; white-space:pre-line;">{{ $description ?? '' }}</p>

    @if(!empty($adminUrl))
        <p style="margin:0 0 6px 0; font-size:14px; color:#104976;">
            View and manage reports in SharpFleet:
        </p>
        <p style="margin:0 0 10px 0;">
            <a href="{{ $adminUrl }}" style="color:#1BA5A5; text-decoration:none;">Open issue/accident reports</a>
        </p>
    @endif

</x-sharpfleet-email-layout>
