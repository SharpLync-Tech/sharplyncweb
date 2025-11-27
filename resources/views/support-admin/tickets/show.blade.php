@extends('support-admin.layouts.base')

@section('title', $ticket->subject)

@section('content')
    <div class="support-admin-header">
        <h1 class="support-admin-title">{{ $ticket->subject }}</h1>
        <a href="{{ route('support-admin.tickets.index') }}" class="support-admin-link-back">
            Back to tickets
        </a>
        <p class="support-admin-subtitle">
            All times shown in AEST (Brisbane time).
        </p>
    </div>

    @if(session('success'))
        <div class="support-admin-alert support-admin-alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="support-admin-ticket-meta-card">
        @include('support-admin.tickets.partials.meta', [
            'ticket' => $ticket,
            'statusOptions' => $statusOptions,
            'priorityOptions' => $priorityOptions
        ])
    </div>

    <div class="support-admin-ticket-layout">
        <div class="support-admin-ticket-main">
            @include('support-admin.tickets.partials.thread', [
                'ticket' => $ticket,
                'messages' => $messages
            ])

            @include('support-admin.tickets.partials.reply', ['ticket' => $ticket])
        </div>

        <aside class="support-admin-ticket-side">
            @include('support-admin.tickets.partials.details', ['ticket' => $ticket])

            @include('support-admin.tickets.partials.notes', [
                'ticket' => $ticket,
                'internalNotes' => $internalNotes
            ])
        </aside>
    </div>
@endsection
