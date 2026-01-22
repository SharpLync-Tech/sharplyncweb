{{-- resources/views/emails/sharpfleet/support-request.blade.php --}}
<x-sharpfleet-email-layout :title="'SharpFleet Support Request'">
    <h1 style="margin:0 0 14px 0; font-size:22px; color:#0A2A4D; font-weight:600;">
        SharpFleet Support Request
    </h1>

    <p style="margin:0 0 18px 0; font-size:15px; color:#104976; line-height:1.6;">
        A new support request was submitted from the SharpFleet web app.
    </p>

    <div style="background:#f8f9fa; padding:18px; border-radius:8px; margin:18px 0;">
        <h3 style="margin:0 0 12px 0; color:#0A2A4D;">Request Details</h3>
        <p style="margin:0 0 8px 0; color:#104976;"><strong>Name:</strong> {{ $name }}</p>
        <p style="margin:0 0 8px 0; color:#104976;"><strong>Email:</strong> {{ $email }}</p>
        <p style="margin:0 0 8px 0; color:#104976;"><strong>Organisation ID:</strong> {{ $organisationId }}</p>
        <p style="margin:0 0 8px 0; color:#104976;"><strong>Organisation Name:</strong> {{ $organisationName }}</p>
        <p style="margin:0 0 8px 0; color:#104976;"><strong>Company Admin:</strong> {{ $adminName }}</p>
        <p style="margin:0 0 8px 0; color:#104976;"><strong>Company Admin Email:</strong> {{ $adminEmail }}</p>
        <p style="margin:0 0 8px 0; color:#104976;"><strong>Platform:</strong> {{ $platform }}</p>
        <p style="margin:0 0 8px 0; color:#104976;"><strong>Usage Mode:</strong> {{ $usageMode }}</p>
        <p style="margin:0 0 8px 0; color:#104976;"><strong>Client Timezone:</strong> {{ $clientTimezone }}</p>
        <p style="margin:0; color:#104976;"><strong>Company Timezone:</strong> {{ $companyTimezone }}</p>
    </div>

    <div style="margin:0 0 18px 0;">
        <h3 style="margin:0 0 10px 0; color:#0A2A4D;">Message</h3>
        <div style="background:#ffffff; border:1px solid #e0e7ef; border-radius:8px; padding:14px; color:#104976;">
            {!! nl2br(e($messageText)) !!}
        </div>
    </div>

    @if(!empty($logs))
        <div style="margin:0 0 18px 0;">
            <h3 style="margin:0 0 10px 0; color:#0A2A4D;">Device Logs</h3>
            <pre style="white-space:pre-wrap; background:#0a2a4d; color:#e6f0ff; padding:14px; border-radius:8px; font-size:12px; line-height:1.5;">{{ $logs }}</pre>
        </div>
    @endif

    <p style="margin:0; font-size:13px; color:#6b7a89;">
        Submitted: {{ $submittedAt }}
    </p>
</x-sharpfleet-email-layout>
