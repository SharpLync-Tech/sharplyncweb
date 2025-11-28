{{-- resources/views/support-admin/tickets/partials/thread.blade.php --}}
{{-- SharpLync Admin Support Thread (Upgraded: matches customer UI flow) --}}

@php
    // Combine original ticket message + replies into a unified timeline
    $allMessages = collect();

    if ($ticket->message) {
        $allMessages->push((object)[
            'id'        => 'ticket-root',
            'is_root'   => true,
            'timestamp' => $ticket->created_at,
            'body'      => $ticket->message,
            'isCustomer'=> true,
            'isAdmin'   => false,
            'authorName'=> $ticket->customerProfile->business_name
                ?? ($ticket->customerUser
                        ? $ticket->customerUser->first_name . ' ' . $ticket->customerUser->last_name
                        : 'Customer'),
        ]);
    }

    foreach ($messages as $msg) {
        $isCustomer = $msg->isCustomer();
        $isAdmin    = $msg->isAdmin();

        // Determine author name
        if ($isCustomer) {
            $authorName = $ticket->customerProfile->business_name
                ?? ($ticket->customerUser
                        ? $ticket->customerUser->first_name . ' ' . $ticket->customerUser->last_name
                        : 'Customer');
        } elseif ($isAdmin) {
            $authorName = $msg->author?->name ?? 'Support Agent';
        } else {
            $authorName = 'Unknown';
        }

        $allMessages->push((object)[
            'id'        => $msg->id,
            'is_root'   => false,
            'timestamp' => $msg->created_at,
            'body'      => $msg->message,
            'isCustomer'=> $isCustomer,
            'isAdmin'   => $isAdmin,
            'authorName'=> $authorName,
        ]);
    }

    // Sort newest → oldest
    $sortedDesc = $allMessages->sortByDesc('timestamp')->values();

    // Latest 2 visible
    $latestTwo = $sortedDesc->take(2);

    // Older messages
    $older = $sortedDesc->slice(2)->sortBy('timestamp')->values();
@endphp

<div class="support-admin-thread-card">

    <div class="support-admin-thread-list">

        {{-- ===========================================
             SHOW LATEST TWO MESSAGES
        ============================================ --}}
        @foreach($latestTwo as $msg)
            @include('support-admin.tickets.partials.message', [
                'isCustomer'               => $msg->isCustomer,
                'authorName'               => $msg->authorName,
                'timestamp'                => $msg->timestamp,
                'body'                     => $msg->body,
                'label'                    => $msg->isCustomer ? 'Customer' : 'Support',

                // NEW
                'message_id'               => $msg->id,
                'attachment_path'          => $msg->attachment_path ?? null,
                'attachment_original_name' => $msg->attachment_original_name ?? null,
                'attachment_mime'          => $msg->attachment_mime ?? null,
            ])

        @endforeach


        {{-- ===========================================
             COLLAPSIBLE: "View earlier conversation(s)"
        ============================================ --}}
        @if($older->isNotEmpty())
            <div class="support-admin-older-wrapper">

                <button type="button"
                        class="support-admin-older-toggle"
                        data-admin-older-toggle>
                    View earlier conversation(s)
                </button>

                <div class="support-admin-older-container"
                     data-admin-older-container
                     hidden>

                    @foreach($older as $msg)
                        @include('support-admin.tickets.partials.message', [
                            'isCustomer' => $msg->isCustomer,
                            'authorName' => $msg->authorName,
                            'timestamp'  => $msg->timestamp,
                            'body'       => $msg->body,
                            'label'      => $msg->isCustomer ? 'Customer' : 'Support',
                        ])
                    @endforeach

                </div>
            </div>
        @endif

    </div>
</div>
