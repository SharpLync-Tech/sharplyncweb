{{-- resources/views/emails/support/ticket-created-internal.blade.php --}}
@extends('emails.layouts.sharplync')

@section('content')
    <h1 style="font-size:20px; margin-bottom:10px;">
        New support ticket received
    </h1>

    <p style="margin:0 0 10px 0;">
        A new support ticket has been created in the customer portal.
    </p>

    <p style="margin:0 0 10px 0;">
        <strong>Reference:</strong> {{ $ticket->reference }}<br>
        <strong>Customer:</strong> {{ $customer->name ?? $customer->full_name ?? ('Customer #' . $customer->id) }}<br>
        @if(!empty($customer->email))
            <strong>Email:</strong> {{ $customer->email }}<br>
        @endif
        <strong>Priority:</strong> {{ ucfirst($ticket->priority) }}<br>
        <strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}<br>
        <strong>Created:</strong>
        {{ $ticket->created_at?->timezone('Australia/Brisbane')->format('d M Y, H:i') }}
        AEST (Brisbane)
    </p>

    <p style="margin:0 0 10px 0;">
        <strong>Subject:</strong><br>
        {{ $ticket->subject }}
    </p>

    <p style="margin:0 0 10px 0;">
        <strong>Message:</strong><br>
        {!! nl2br(e($ticket->message)) !!}
    </p>
@endsection
