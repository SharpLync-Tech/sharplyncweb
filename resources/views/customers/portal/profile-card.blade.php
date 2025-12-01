{{-- 
  Partial: customers/portal/profile-card.blade.php
  Usage: Left column profile card on Customer Portal
--}}

<div class="cp-profile-card">
    <div class="cp-profile-header">
        <div class="cp-avatar">
            @php
                $photo = $user->profile_photo ? asset('storage/'.$user->profile_photo) : null;
                // Fixed typo: last_name instead of last_lastname
                $initials = strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1));
            @endphp
            
            @if($photo)
                <img id="current-avatar" src="{{ $photo }}" alt="Avatar">
            @else
                <span id="current-avatar-initials">{{ $initials }}</span>
            @endif
        </div>

        <div class="cp-name-group">
            <h3>{{ $fullName }}</h3>
            <p class="cp-member-status">{{ $status }}</p>
            <p class="cp-detail-line">
                Email: <a href="mailto:{{ $email }}">{{ $email }}</a>
            </p>

            @if($since)
                <p class="cp-detail-line">Customer since: {{ $since }}</p>
            @endif

            {{-- Support PIN Preview --}}
            @if(isset($u->support_pin))
                <p class="cp-detail-line">
                    Support PIN: 
                    <span id="cp-sspin-preview">
                        •••••• 
                    </span>
                    <button 
                        id="cp-open-password-modal-from-preview" 
                        class="cp-btn cp-small-btn cp-navy-btn" 
                        style="margin-left: .75rem; padding: .25rem .7rem; font-size: .75rem;">
                        Manage
                    </button>
                </p>
            @endif
        </div>
    </div>

    <div class="cp-profile-actions">
        <a href="{{ route('customer.profile.edit') }}" class="cp-btn cp-edit-profile">
            Edit Profile
        </a>
    </div>
</div>
