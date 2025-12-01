{{-- ================================================================
   CUSTOMER PORTAL — PROFILE CARD
   File: resources/views/customers/portal/profile-card.blade.php
   Version: 3.1 (SSPIN Preview + Create Button)
   ================================================================= --}}

<div class="cp-profile-card">
    <div class="cp-profile-header">

        {{-- =======================
            AVATAR + NAME BLOCK
           ======================= --}}
        <div class="cp-avatar">
            @php
                $photo = $user->profile_photo ? asset('storage/'.$user->profile_photo) : null;
                $initials = strtoupper(substr($user->first_name,0,1) . substr($user->last_name,0,1));
            @endphp

            @if($photo)
                <img src="{{ $photo }}" alt="Avatar">
            @else
                <span>{{ $initials }}</span>
            @endif
        </div>

        <div class="cp-name-group">
            <h3>{{ $fullName }}</h3>
            <p class="cp-member-status">{{ $status }}</p>
            <p class="cp-detail-line">Email: <a href="mailto:{{ $email }}">{{ $email }}</a></p>

            @if($since)
                <p class="cp-detail-line">Customer since: {{ $since }}</p>
            @endif

            {{-- ===========================================================
               SSPIN PREVIEW BLOCK   (REPLACE THIS WHOLE BLOCK IF NEEDED)
               =========================================================== --}}
            <p class="cp-detail-line" style="margin-top:.35rem;">
                Support PIN:

                @if(!$u->support_pin)
                    <span style="color:#777;">Not set</span>

                    <button
                        id="cp-create-sspin-btn"
                        class="cp-btn cp-small-btn cp-teal-btn"
                        style="padding:.25rem .6rem; font-size:.75rem; margin-left:.5rem;">
                        Create
                    </button>

                @else
                    <span id="cp-sspin-preview" style="letter-spacing:.3rem;">
                        ••••••
                    </span>

                    <button
                        id="cp-open-password-modal-from-preview"
                        class="cp-btn cp-small-btn cp-navy-btn"
                        style="padding:.25rem .6rem; font-size:.75rem; margin-left:.6rem;">
                        Manage
                    </button>
                @endif
            </p>
            {{-- =========================================================== --}}
        </div>

    </div>

    <div class="cp-profile-actions">
        <a href="{{ route('customer.profile.edit') }}" class="cp-btn cp-edit-profile">Edit Profile</a>
    </div>
</div>
