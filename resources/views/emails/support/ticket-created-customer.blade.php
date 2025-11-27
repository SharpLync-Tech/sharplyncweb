{{-- resources/views/emails/support/ticket-created-customer.blade.php --}}
@extends('emails.layouts.sharplync')

@section('content')
    <h1 style="font-size:20px; margin-bottom:10px;">
        We’ve received your support request
    </h1>

    <p style="margin:0 0 10px 0;">
        Hi {{ $customer->name ?? $customer->full_name ?? 'there' }},
    </p>

    <p style="margin:0 0 10px 0;">
        Thanks for reaching out to SharpLync. Your support request has been logged with the details below.
    </p>

    <p style="margin:0 0 10px 0;">
        <strong>Reference:</strong> {{ $ticket->reference }}<br>
        <strong>Subject:</strong> {{ $ticket->subject }}<br>
        <strong>Priority:</strong> {{ ucfirst($ticket->priority) }}<br>
        <strong>Created:</strong>
        {{ $ticket->created_at?->timezone('Australia/Brisbane')->format('d M Y, H:i') }}
        AEST (Brisbane)
    </p>

    <p style="margin:0 0 10px 0;">
        <strong>Your message:</strong><br>
        {!! nl2br(e($ticket->message)) !!}
    </p>

    <p style="margin:20px 0 0 0;">
        You can view and reply to this ticket at any time in your SharpLync customer portal.
    </p>
@endsection
