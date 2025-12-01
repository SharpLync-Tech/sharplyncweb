@extends('support-admin.layouts.base')

@section('title', $ticket->subject)

@push('styles')
    <link href="{{ secure_asset('quill/quill.core.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('quill/quill.snow.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('quill/quill-emoji.css') }}" rel="stylesheet">

    <style>
        /* ======================================================
           ADMIN STATUS DOT — RED PULSING (WAITING ON SUPPORT)
        =======================================================*/
        .admin-status-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-status-dot {
            width: 12px;
            height: 12px;
            background: #ff3b30; /* Red */
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(255, 59, 48, 0.6);
            animation: admin-pulse 1.2s infinite ease-in-out;
        }

        @keyframes admin-pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 6px rgba(255, 59, 48, 0.6);
            }
            50% {
                transform: scale(1.35);
                box-shadow: 0 0 14px rgba(255, 59, 48, 0.9);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 6px rgba(255, 59, 48, 0.6);
            }
        }
    </style>
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

        {{-- ================================
             META + STATUS DOT (new)
        ================================= --}}
        <div class="admin-status-wrapper">

            {{-- FLASHING DOT ONLY WHEN SUPPORT MUST REPLY --}}
            @if($ticket->status === 'waiting_on_support')
                <span class="admin-status-dot"></span>
            @endif

            @include('support-admin.tickets.partials.meta', [
                'ticket' => $ticket,
                'statusOptions' => $statusOptions,
                'priorityOptions' => $priorityOptions
            ])
        </div>
    </div>

    <div class="support-admin-ticket-layout">

        <div class="support-admin-ticket-main">

            {{-- Reply box --}}
            @include('support-admin.tickets.partials.reply', ['ticket' => $ticket])

            {{-- Thread --}}
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
    <script src="{{ secure_asset('quill/quill.min.js') }}"></script>
    <script src="{{ secure_asset('quill/quill-emoji.js') }}"></script>

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
                        "emoji-shortname": true,
                    }
                });

                const form = quillEl.closest('form');
                form.addEventListener('submit', function () {
                    hiddenInput.value = quill.root.innerHTML;
                });
            }
        });
    </script>
@endpush
