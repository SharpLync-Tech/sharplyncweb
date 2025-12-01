{{-- ============================================================
     FILE: profile-card.blade.php
     PURPOSE: Customer profile left column
     UPDATED SECTIONS ARE CLEARLY MARKED
   ============================================================ --}}

<div class="cp-profile-card">
    <div class="cp-profile-header">

        {{-- Avatar --}}
        <div class="cp-avatar">
            @php
                $photo = $user->profile_photo ? asset('storage/'.$user->profile_photo) : null;
                $initials = strtoupper(substr($user->first_name,0,1) . substr($user->last_name,0,1));
            @endphp

            @if($photo)
                <img id="current-avatar" src="{{ $photo }}" alt="Avatar">
            @else
                <span id="current-avatar-initials">{{ $initials }}</span>
            @endif
        </div>

        <div class="cp-name-group">
            <h3>{{ $user->first_name }} {{ $user->last_name }}</h3>
            <p class="cp-member-status">{{ ucfirst($user->account_status ?? 'Active') }}</p>

            <p class="cp-detail-line">
                Email: <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
            </p>

            <p class="cp-detail-line">
                Customer since: {{ $user->created_at->format('F Y') }}
            </p>

            {{-- ============================================================
                 🔥 UPDATED SECTION #1 — SSPIN PREVIEW ON DASHBOARD
                 This is the ONLY new part for this file.
               ============================================================ --}}
            @if(isset($user->support_pin))
                <p class="cp-detail-line" style="margin-top: .4rem;">
                    Support PIN:
                    <span id="cp-sspin-preview">••••••</span>

                    <button 
                        id="cp-open-password-modal-from-preview"
                        class="cp-btn cp-small-btn cp-navy-btn"
                        style="margin-left:.75rem; padding:.25rem .7rem; font-size:.75rem;">
                        Manage
                    </button>
                </p>
            @endif
            {{-- ============================================================ --}}

        </div>
    </div>

    <div class="cp-profile-actions">
        <a href="{{ route('customer.profile.edit') }}" class="cp-btn cp-edit-profile">Edit Profile</a>
    </div>
</div>
