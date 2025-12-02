{{-- =================================================================== --}}
{{--  SharpLync Customer Portal — Password & SSPIN Modal                 --}}
{{--  File: resources/views/customers/portal/modals/password-sspin-modal --}}
{{--  NOTE: Only PASSWORD SECTION has been changed in this step          --}}
{{-- =================================================================== --}}

<div id="cp-password-modal" class="cp-modal-backdrop" aria-hidden="true">
    <div class="cp-modal-sheet">

        <header class="cp-modal-header">
            <div>
                <h3>Password & SSPIN Settings</h3>
                <p class="cp-modal-subtitle">
                    Update your login password or Support PIN.
                </p>
            </div>
            <button class="cp-password-close">&times;</button>
        </header>

        <div class="cp-modal-body">

            {{-- ======================================================= --}}
            {{-- [SECTION A] CHANGE PASSWORD DIRECTLY (NO EMAIL FLOW)    --}}
            {{-- ======================================================= --}}
            <div class="cp-sec-card">

                <h4>Change Password</h4>

                <p class="cp-sec-desc" style="margin-bottom: .75rem;">
                    Set a new password for your SharpLync account.
                </p>

                <label class="cp-sec-label">New Password</label>
                <input 
                    type="password" 
                    id="cp-new-password" 
                    class="cp-input" 
                    placeholder="Enter new password">
                    <div id="cp-pass-strength-wrapper" style="margin-top:6px;">
                    <div id="cp-pass-strength-bar"
                        style="height:6px; width:100%; border-radius:4px; background:#e5e5e5; overflow:hidden;">
                        <div id="cp-pass-strength-fill"
                            style="height:100%; width:0%; background:#2CBFAE; transition:width .25s;"></div>
                    </div>
                    <div id="cp-pass-strength-text"
                        style="margin-top:4px; font-size:.85rem; color:#63718a;">
                        <!-- dynamic -->
                    </div>
                </div>


                <label class="cp-sec-label" style="margin-top:.75rem;">Confirm New Password</label>
                <input 
                    type="password" 
                    id="cp-confirm-password" 
                    class="cp-input" 
                    placeholder="Re-enter new password">

                <button
                    id="cp-password-save"
                    class="cp-btn cp-teal-btn"
                    style="margin-top:1rem;">
                    Save New Password
                </button>

            </div>


            {{-- ======================================================= --}}
            {{-- [SECTION B] SSPIN MANAGEMENT (UNCHANGED FUNCTIONALLY)   --}}
            {{--    - Uses existing SSPIN logic                          --}}
            {{--    - Still fully controlled by JS & SecurityController  --}}
            {{-- ======================================================= --}}
            <div class="cp-sec-card">

                <h4>Support PIN (SSPIN)</h4>

                <p class="cp-sec-desc" style="margin-bottom:.3rem;">
                    Your current Support PIN:
                </p>

                {{-- Current SSPIN value (shown in clear once logged in) --}}
                <div
                    id="cp-sspin-display"
                    style="font-size: 2rem; font-weight: 700; color:#0A2A4D; letter-spacing:.45rem; margin:.5rem 0 1rem;">
                    {{ $u->sspin ?? '------' }}
                </div>

                <div style="display:flex; gap:.75rem; margin-bottom:1rem;">
                    {{-- Generate new random 6-digit PIN (handled in JS) --}}
                    <button id="cp-sspin-generate" class="cp-btn cp-teal-btn">
                        Generate New PIN
                    </button>
                </div>

                <label class="cp-sec-label" style="margin-top:.25rem;">
                    Enter new PIN
                </label>

                <input
                    type="text"
                    maxlength="6"
                    class="cp-input"
                    id="cp-sspin-input"
                    value="{{ $u->sspin ?? '' }}"
                    placeholder="123456">

                <button
                    id="cp-sspin-save"
                    class="cp-btn cp-teal-btn"
                    style="margin-top:1rem;">
                    Save SSPIN
                </button>
            </div>

        </div>

        <footer class="cp-modal-footer">
            <button class="cp-btn cp-small-btn cp-navy-btn cp-password-close">
                Close
            </button>
        </footer>

    </div>
</div>
