{{-- =================================================================== --}}
{{--  SharpLync Customer Portal — Profile Card (with SSPIN Preview)      --}}
{{--  File: resources/views/customers/portal/profile-card.blade.php      --}}
{{-- =================================================================== --}}

@php
    $sspin = $u->sspin ?? null;
@endphp

<div class="cp-profile-card">
    <div class="cp-profile-header">

        {{-- Avatar --}}
        <div class="cp-avatar">
            @php
                $photo = $u->profile_photo ? asset('storage/'.$u->profile_photo) : null;
                $initials = strtoupper(substr($u->first_name,0,1) . substr($u->last_name,0,1));
            @endphp

            @if($photo)
                <img id="current-avatar" src="{{ $photo }}" alt="Avatar">
            @else
                <span id="current-avatar-initials">{{ $initials }}</span>
            @endif
        </div>

        {{-- Name & Details --}}
        <div class="cp-name-group">
            <h3>{{ $fullName }}</h3>
            <p class="cp-member-status">{{ $status }}</p>

            <p class="cp-detail-line">
                Email: <a href="mailto:{{ $email }}">{{ $email }}</a>
            </p>

            @if($since)
                <p class="cp-detail-line">Customer since: {{ $since }}</p>
            @endif

            {{-- =============================================================== --}}
            {{-- SSPIN PREVIEW (Create / Manage)                                 --}}
            {{-- =============================================================== --}}
            <p class="cp-detail-line" style="margin-top:.35rem;">
                Support PIN:
                @if(!$sspin)
                    <span style="color:#555;">Not set</span>

                    {{-- CREATE BUTTON --}}
                    <button
                        id="cp-open-password-modal-from-preview"
                        class="cp-btn cp-small-btn cp-teal-btn"
                        style="margin-left:.5rem; padding:.28rem .75rem; font-size:.75rem;">
                        Create
                    </button>

                @else
                    {{-- Masked preview --}}
                    <span id="cp-sspin-preview" style="letter-spacing:.20rem;">
                        •• •• ••
                    </span>

                    {{-- MANAGE BUTTON --}}
                    <button
                        id="cp-open-password-modal-from-preview"
                        class="cp-btn cp-small-btn cp-navy-btn"
                        style="margin-left:.75rem; padding:.28rem .75rem; font-size:.75rem;">
                        Manage
                    </button>
                @endif
            </p>
        </div>
    </div>

    <div class="cp-profile-actions">
        <a href="{{ route('customer.profile.edit') }}" class="cp-btn cp-edit-profile">Edit Profile</a>
    </div>
</div>
