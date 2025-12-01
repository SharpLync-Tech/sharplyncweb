{{-- =================================================================== --}}
{{--  SharpLync Customer Portal — Profile Card (with SSPIN Preview)      --}}
{{--  File: resources/views/customers/portal/profile-card.blade.php      --}}
{{-- =================================================================== --}}

@php
    $sspin = $u->sspin ?? null;
@endphp

<div class="cp-profile-card">
    <div class="cp-profile-header">

        {{-- =============================================================== --}}
        {{-- AVATAR                                                            --}}
        {{-- =============================================================== --}}
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

        {{-- =============================================================== --}}
        {{-- NAME + STATUS + DETAILS                                          --}}
        {{-- =============================================================== --}}
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
            {{-- SSPIN PREVIEW — UPDATED FOR LIVE JS (FINAL VERSION)            --}}
            {{-- =============================================================== --}}
            <p class="cp-detail-line" style="margin-top: .35rem;">
                Support PIN:

                {{-- ALWAYS show preview span with ID --}}
                <span id="cp-sspin-preview" style="margin-left: .25rem;">
                    @if(!$sspin)
                        Not set
                    @else
                        {{ str_repeat('•', strlen($sspin)) }}
                    @endif
                </span>

                {{-- ACTION BUTTON (create/manage) --}}
                <button
                    id="cp-open-password-modal-from-preview"
                    class="cp-btn cp-small-btn cp-teal-btn"
                    style="margin-left:.65rem; padding:.28rem .75rem; font-size:.75rem;">
                    @if(!$sspin)
                        Create
                    @else
                        Manage
                    @endif
                </button>
            </p>

            {{-- =============================================================== --}}
            {{-- END SSPIN PREVIEW                                               --}}
            {{-- =============================================================== --}}

        </div>
    </div>

    {{-- EDIT PROFILE BUTTON --}}
    <div class="cp-profile-actions">
        <a href="{{ route('customer.profile.edit') }}"
           class="cp-btn cp-edit-profile">Edit Profile</a>
    </div>
</div>
