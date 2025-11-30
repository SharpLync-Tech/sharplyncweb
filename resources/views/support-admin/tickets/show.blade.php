@extends('support-admin.layouts.base')

@section('title', $ticket->subject)

@push('styles')
    {{-- Quill CSS --}}
    <link href="{{ secure_asset('quill/quill.core.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('quill/quill.snow.css') }}" rel="stylesheet">

    {{-- Emoji plugin --}}
    <link href="{{ secure_asset('quill/quill-emoji.css') }}" rel="stylesheet">
@endpush

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

            {{-- Reply box (Now Quill-powered) --}}
            @include('support-admin.tickets.partials.reply', ['ticket' => $ticket])

            {{-- Thread after reply box --}}
            @include('support-admin.tickets.partials.thread', [
                'ticket' => $ticket,
                'messages' => $messages
            ])

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

@push('scripts')
    {{-- Quill Core --}}
    <script src="{{ secure_asset('quill/quill.min.js') }}"></script>

    {{-- Emoji plugin --}}
    <script src="{{ secure_asset('quill/quill-emoji.js') }}"></script>

    {{-- Admin Quill initialiser --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const quillEl = document.getElementById('admin-quill-editor');
            const hiddenInput = document.getElementById('admin-quill-html');

            if (quillEl && hiddenInput) {
                const quill = new Quill('#admin-quill-editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: {
                            container: '#admin-quill-toolbar'
                        },
                        "emoji-toolbar": true,
                        "emoji-textarea": true,
                        "emoji-shortname": true
                    }
                });

                // Push HTML into hidden input before POST
                const form = quillEl.closest('form');
                form.addEventListener('submit', function () {
                    hiddenInput.value = quill.root.innerHTML;
                });
            }
        });
    </script>
@endpush
