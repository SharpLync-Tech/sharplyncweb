{{-- resources/views/emails/sharpfleet/fuel-receipt.blade.php --}}
<x-sharpfleet-email-layout :title="'Fuel Receipt'">
    <h1 style="margin:0 0 14px 0; font-size:22px; color:#0A2A4D; font-weight:600;">
        Fuel Receipt Uploaded
    </h1>

    <p style="margin:0 0 18px 0; font-size:15px; color:#104976; line-height:1.6;">
        A new fuel receipt was submitted from the SharpFleet mobile app.
    </p>

    <div style="background:#f8f9fa; padding:18px; border-radius:8px; margin:18px 0;">
        <h3 style="margin:0 0 12px 0; color:#0A2A4D;">Receipt Details</h3>
        <p style="margin:0 0 8px 0; color:#104976;"><strong>Organisation:</strong> {{ $organisationName }}</p>
        <p style="margin:0 0 8px 0; color:#104976;"><strong>Vehicle:</strong> {{ $vehicleLabel }}</p>
        <p style="margin:0 0 8px 0; color:#104976;"><strong>Driver:</strong> {{ $driverName }}</p>
        <p style="margin:0 0 8px 0; color:#104976;"><strong>Driver Email:</strong> {{ $driverEmail }}</p>
        <p style="margin:0; color:#104976;"><strong>Odometer:</strong> {{ $odometerReading }}</p>
    </div>

    <p style="margin:0; font-size:13px; color:#6b7a89;">
        Submitted: {{ $submittedAt }}
    </p>
</x-sharpfleet-email-layout>
