<div class="support-admin-thread-card">
    <div class="support-admin-thread-list">
        @if($ticket->message)
            @include('support-admin.tickets.partials.message', [
                'isCustomer' => true,
                'authorName' => $ticket->customerProfile->business_name
                    ?? ($ticket->customerUser ? $ticket->customerUser->first_name . ' ' . $ticket->customerUser->last_name : 'Customer'),
                'timestamp'  => $ticket->created_at,
                'body'       => $ticket->message,
                'label'      => 'Ticket created',
            ])
        @endif

        @forelse($messages as $msg)
            @php
                // New logic using user_type + author()

                $isCustomer = $msg->isCustomer();
                $isAdmin    = $msg->isAdmin();

                // Name for display
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

            @endphp

            @include('support-admin.tickets.partials.message', [
                'isCustomer' => $isCustomer,
                'authorName' => $authorName,
                'timestamp'  => $msg->created_at,
                'body'       => $msg->message,
                'label'      => $isCustomer ? 'Customer' : 'Support',
            ])
        @empty
            @if(!$ticket->message)
                <div class="support-admin-empty">
                    <p class="support-admin-empty-title">No messages yet</p>
                    <p class="support-admin-empty-text">
                        Use the reply box below to start the conversation.
                    </p>
                </div>
            @endif
        @endforelse

    </div>
</div>
