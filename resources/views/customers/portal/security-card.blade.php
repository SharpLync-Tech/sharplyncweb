{{-- 
  Partial: customers/portal/security-card.blade.php
  Usage: Security card in right column
--}}

<div class="cp-activity-card cp-security-card">
    <h4>Security</h4>
    <p>Manage your login security and two-factor authentication options.</p>

    <div class="cp-security-footer" style="display:flex; gap:.5rem; flex-wrap:wrap;">

        {{-- 2FA SETTINGS BUTTON --}}
        <button id="cp-open-security-modal" class="cp-btn cp-small-btn cp-teal-btn">
            2FA Settings
        </button>

        {{-- PASSWORD & SSPIN SETTINGS BUTTON --}}
        <button id="cp-open-password-modal" class="cp-btn cp-small-btn cp-teal-btn">
            Password & SSPIN Settings
        </button>


    </div>
</div>
