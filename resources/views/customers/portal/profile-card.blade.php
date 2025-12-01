{{-- resources/views/customers/portal/profile-card.blade.php --}}
<div class="cp-profile-card">

    <div class="cp-profile-header">

        {{-- Avatar --}}
        <div class="cp-avatar">
            @php
                $photo = $user->profile_photo ? asset('storage/'.$user->profile_photo) : null;
                $initials = strtoupper(
                    substr($user->first_name,0,1) .
                    substr($user->last_name,0,1)
                );
            @endphp

            @if($photo)
                <img id="current-avatar" src="{{ $photo }}" alt="Avatar">
            @else
                <span id="current-avatar-initials">{{ $initials }}</span>
            @endif
        </div>

        {{-- Name + Details --}}
        <div class="cp-name-group">
            <h3>{{ $fullName }}</h3>
            <p class="cp-member-status">{{ $status }}</p>

            <p class="cp-detail-line">
                Email: <a href="mailto:{{ $email }}">{{ $email }}</a>
            </p>

            @if($since)
                <p class="cp-detail-line">Customer since: {{ $since }}</p>
            @endif

            {{-- ================================ --}}
            {{-- SSPIN PREVIEW (OPTION C1)        --}}
            {{-- ================================ --}}
            @if(isset($u->support_pin))
                <p class="cp-detail-line" 
                   style="display:flex; align-items:center; gap:.5rem; margin-top:.35rem;">

                    <span>Support PIN:</span>

                    {{-- Masked SSPIN --}}
                    <span id="cp-sspin-preview" 
                          style="letter-spacing:.3rem; font-weight:600;">
                        ••••••
                    </span>

                    {{-- Manage button --}}
                    <button 
                        id="cp-open-password-modal-from-preview"
                        class="cp-btn cp-small-btn cp-navy-btn"
                        style="padding:.25rem .65rem; font-size:.75rem;">
                        Manage
                    </button>
                </p>
            @endif

        </div>
    </div>

    {{-- Profile Actions --}}
    <div class="cp-profile-actions">
        <a href="{{ route('customer.profile.edit') }}" 
           class="cp-btn cp-edit-profile">
           Edit Profile
        </a>
    </div>

</div>
