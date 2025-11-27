<div class="card shadow-sm admin-ticket-conversation-card">
    <div class="card-header bg-white border-0 pb-0">
        <h6 class="mb-1 fw-semibold">Conversation</h6>
        <p class="text-muted small mb-0">
            Messages between the customer and SharpLync support.
        </p>
    </div>
    <div class="card-body ticket-conversation-wrapper">

        {{-- Original ticket message --}}
        @if($ticket->message)
            @include('admin.support.tickets.partials.message', [
                'isCustomer' => true,
                'authorName' => $ticket->customerProfile->business_name
                    ?? ($ticket->customerUser ? $ticket->customerUser->first_name . ' ' . $ticket->customerUser->last_name : 'Customer'),
                'timestamp'  => $ticket->created_at,
                'body'       => $ticket->message,
                'label'      => 'Ticket created',
            ])
        @endif

        {{-- Replies --}}
        @forelse($messages as $msg)
            @php
                $isCustomer = $msg->is_internal ? false : ($msg->customer_id !== null && $msg->admin_id === null);
                $authorName = $isCustomer
                    ? ($ticket->customerProfile->business_name
                        ?? ($ticket->customerUser ? $ticket->customerUser->first_name . ' ' . $ticket->customerUser->last_name : 'Customer'))
                    : ($msg->admin?->name ?? 'SharpLync Support');
            @endphp

            @include('admin.support.tickets.partials.message', [
                'isCustomer' => $isCustomer,
                'authorName' => $authorName,
                'timestamp'  => $msg->created_at,
                'body'       => $msg->message,
                'label'      => $isCustomer ? 'Customer' : 'Support',
            ])
        @empty
            @if(!$ticket->message)
                <div class="text-center text-muted py-4">
                    <i class="bi bi-chat-square-dots display-6 d-block mb-2"></i>
                    <p class="mb-1 fw-semibold">No messages yet</p>
                    <p class="small mb-0">
                        Use the reply box below to start the conversation.
                    </p>
                </div>
            @endif
        @endforelse
    </div>
</div>
