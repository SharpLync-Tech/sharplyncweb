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

            {{-- =============================== --}}
            {{-- SSPIN PREVIEW (ALWAYS SHOWN)   --}}
            {{-- =============================== --}}
            <p class="cp-detail-line" style="display:flex; align-items:center; gap:.5rem; margin-top:.35rem;">

                <span>Support PIN:</span>

                @if(!empty($u->support_pin))
                    {{-- Masked PIN --}}
                    <span id="cp-sspin-preview" style="letter-spacing:.3rem; font-weight:600;">
                        ••••••
                    </span>

                    {{-- Manage button --}}
                    <button 
                        id="cp-open-password-modal-from-preview"
                        class="cp-btn cp-small-btn cp-navy-btn" 
                        style="padding:.25rem .7rem; font-size:.75rem;">
                        Manage
                    </button>

                @else
                    {{-- No PIN set --}}
                    <span style="font-weight:600; color:#888;">Not set</span>

                    {{-- Create button --}}
                    <button 
                        id="cp-open-password-modal-from-preview"
                        class="cp-btn cp-small-btn cp-teal-btn" 
                        style="padding:.25rem .7rem; font-size:.75rem;">
                        Create
                    </button>
                @endif

            </p>

            {{-- ============================================================ --}}

        </div>
    </div>

    <div class="cp-profile-actions">
        <a href="{{ route('customer.profile.edit') }}" class="cp-btn cp-edit-profile">Edit Profile</a>
    </div>
</div>
