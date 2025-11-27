{{-- resources/views/emails/support/ticket-priority-updated.blade.php --}}
@extends('emails.layouts.sharplync')

@section('content')
    <h1 style="font-size:20px; margin-bottom:10px;">
        Priority updated on your support ticket
    </h1>

    <p style="margin:0 0 10px 0;">
        Hi {{ $customer->name ?? $customer->full_name ?? 'there' }},
    </p>

    <p style="margin:0 0 10px 0;">
        The priority of your support ticket <strong>{{ $ticket->reference }}</strong> has been updated.
    </p>

    <p style="margin:0 0 10px 0;">
        <strong>Previous priority:</strong> {{ ucfirst($oldPriority) }}<br>
        <strong>New priority:</strong> {{ ucfirst($newPriority) }}
    </p>

    <p style="margin:0 0 10px 0;">
        <strong>Subject:</strong> {{ $ticket->subject }}
    </p>

    <p style="margin:20px 0 0 0;">
        You can view the latest updates and reply in your SharpLync customer portal.
    </p>
@endsection
