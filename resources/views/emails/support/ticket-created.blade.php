{{-- resources/views/emails/support/ticket-created.blade.php --}}
{{-- Uses your main email layout: emails.layouts.sharplync --}}

@extends('emails.layouts.sharplync')

@section('content')
    <h1 style="font-size:20px; margin-bottom:10px;">
        @if($isInternal ?? false)
            New support ticket received
        @else
            We’ve received your support request
        @endif
    </h1>

    <p style="margin:0 0 10px 0;">
        @if(!($isInternal ?? false))
            Hi {{ $customer->name ?? 'there' }},
        @else
            A new ticket has been created by {{ $customer->name ?? 'a customer' }}.
        @endif
    </p>

    <p style="margin:0 0 10px 0;">
        <strong>Reference:</strong> {{ $ticket->reference }}<br>
        <strong>Subject:</strong> {{ $ticket->subject }}<br>
        <strong>Priority:</strong> {{ ucfirst($ticket->priority) }}
    </p>

    <p style="margin:0 0 10px 0;">
        {!! nl2br(e($ticket->message)) !!}
    </p>

    @if(!($isInternal ?? false))
        <p style="margin:20px 0 0 0;">
            We’ll review your request and get back to you as soon as possible.
        </p>
    @endif
@endsection
