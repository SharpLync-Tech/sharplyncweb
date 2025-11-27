@php
    $profile = $ticket->customerProfile;
    $user    = $ticket->customerUser;
@endphp

<div class="support-admin-side-card">
    <h3 class="support-admin-side-title">Customer</h3>

    <div class="support-admin-side-body">
        <p class="support-admin-side-line">
            <span class="support-admin-side-label">Name</span>
            <span class="support-admin-side-value">
                {{ $profile->business_name ?? ($user ? $user->first_name . ' ' . $user->last_name : 'Customer') }}
            </span>
        </p>

        @if($user)
            <p class="support-admin-side-line">
                <span class="support-admin-side-label">Email</span>
                <span class="support-admin-side-value">{{ $user->email }}</span>
            </p>
        @endif

        @if($profile?->mobile_number)
            <p class="support-admin-side-line">
                <span class="support-admin-side-label">Mobile</span>
                <span class="support-admin-side-value">{{ $profile->mobile_number }}</span>
            </p>
        @endif

        <p class="support-admin-side-line">
            <span class="support-admin-side-label">Ticket ID</span>
            <span class="support-admin-side-value">#{{ $ticket->id }}</span>
        </p>

        @if($ticket->created_via)
            <p class="support-admin-side-line">
                <span class="support-admin-side-label">Created via</span>
                <span class="support-admin-side-value">
                    {{ ucfirst(str_replace('_',' ', $ticket->created_via)) }}
                </span>
            </p>
        @endif
    </div>
</div>
