{{-- resources/views/emails/support/ticket-replied.blade.php --}}
{{-- Uses your main email layout: emails.layouts.sharplync --}}

@extends('emails.layouts.sharplync')

@section('content')
    <h1 style="font-size:20px; margin-bottom:10px;">
        @if($isInternal ?? false)
            Customer replied to support ticket
        @else
            Update on your support ticket
        @endif
    </h1>

    <p style="margin:0 0 10px 0;">
        <strong>Reference:</strong> {{ $ticket->reference }}<br>
        <strong>Subject:</strong> {{ $ticket->subject }}
    </p>

    <p style="margin:0 0 10px 0;">
        New reply:
    </p>

    <p style="margin:0 0 10px 0;">
        {!! nl2br(e($reply->message)) !!}
    </p>

    @if(!($isInternal ?? false))
        <p style="margin:20px 0 0 0;">
            You can view and reply to this ticket in your customer portal.
        </p>
    @endif
@endsection
