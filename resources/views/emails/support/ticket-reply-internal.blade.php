{{-- resources/views/emails/support/ticket-reply-internal.blade.php --}}
@extends('emails.layouts.sharplync')

@section('content')
    <h1 style="font-size:20px; margin-bottom:10px;">
        Customer reply on ticket {{ $ticket->reference }}
    </h1>

    <p style="margin:0 0 10px 0;">
        {{ $customer->name ?? $customer->full_name ?? ('Customer #' . $customer->id) }} has added a reply to this ticket.
    </p>

    <p style="margin:0 0 10px 0;">
        <strong>Reference:</strong> {{ $ticket->reference }}<br>
        <strong>Subject:</strong> {{ $ticket->subject }}<br>
        <strong>Priority:</strong> {{ ucfirst($ticket->priority) }}<br>
        <strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
    </p>

    <p style="margin:0 0 10px 0;">
        <strong>Reply:</strong><br>
        {!! nl2br(e($reply->message)) !!}
    </p>

    <p style="margin:20px 0 0 0;">
        You can respond to this ticket from the SharpLync admin portal.
    </p>
@endsection
