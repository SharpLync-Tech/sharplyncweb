{{-- resources/views/emails/sharpfleet/new-subscriber.blade.php --}}
<x-email-layout :title="'New SharpFleet Subscriber'">

    <h1 style="margin:0 0 15px 0; font-size:22px; color:#0A2A4D; font-weight:600;">
        New SharpFleet Subscriber
    </h1>

    <p style="margin:0 0 20px 0; font-size:15px; color:#104976; line-height:1.6;">
        A new user has successfully registered and activated their SharpFleet account.
    </p>

    <div style="background:#f8f9fa; padding:20px; border-radius:8px; margin:20px 0;">
        <h3 style="margin:0 0 15px 0; color:#0A2A4D;">Subscriber Details:</h3>
        <p style="margin:0 0 8px 0; color:#104976;"><strong>Email:</strong> {{ $email }}</p>
        <p style="margin:0 0 8px 0; color:#104976;"><strong>Business Type:</strong> {{ $businessTypeLabel }}</p>
        <p style="margin:0; color:#104976;"><strong>Registration Date:</strong> {{ date('F j, Y \a\t g:i A') }}</p>
    </div>

    <p style="margin:0 0 15px 0; font-size:15px; color:#104976;">
        The user has started their 30-day free trial and can now access the SharpFleet dashboard.
    </p>

    <p style="margin:0; font-size:14px; color:#6b7a89;">
        You may want to follow up with this subscriber to ensure they have everything they need to get started.
    </p>

</x-email-layout>