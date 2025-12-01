{{-- ================================================================
   PASSWORD + SSPIN MODAL
   File: resources/views/customers/portal/modals/password-sspin-modal.blade.php
   Version: 3.1 (Works with Create SSPIN button)
   ================================================================= --}}

<div id="cp-password-modal" class="cp-modal-backdrop" aria-hidden="true">
    <div class="cp-modal-sheet">

        <header class="cp-modal-header">
            <div>
                <h3>Password Settings</h3>
                <p class="cp-modal-subtitle">Update your login password or Support PIN.</p>
            </div>
            <button class="cp-password-close">&times;</button>
        </header>

        <div class="cp-modal-body">

            {{-- ================================
               PASSWORD SECTION
               ================================ --}}
            <div class="cp-sec-card">

                <h4>Change Password</h4>

                <label class="cp-sec-label">Current Password</label>
                <input type="password" class="cp-input" id="cp-pass-current">

                <label class="cp-sec-label" style="margin-top:.55rem;">New Password</label>
                <input type="password" class="cp-input" id="cp-pass-new">

                <label class="cp-sec-label" style="margin-top:.55rem;">Confirm Password</label>
                <input type="password" class="cp-input" id="cp-pass-confirm">

                <div style="display:flex; gap:.75rem; margin-top:1rem;">
                    <button class="cp-btn cp-teal-btn">Update Password</button>
                    <button class="cp-btn cp-navy-btn">I Forgot My Password</button>
                </div>
            </div>

            {{-- ================================
               SSPIN SECTION
               ================================ --}}
            <div class="cp-sec-card" style="margin-top:1.2rem;">

                <h4>Support PIN (SSPIN)</h4>

                <p class="cp-sec-desc" style="margin-bottom:.3rem;">Your current Support PIN:</p>

                <div id="cp-sspin-display" style="font-size:2rem; letter-spacing:.4rem; font-weight:700;">
                    ••••••
                </div>

                <div style="display:flex; gap:.75rem; margin-top:.75rem;">
                    <button id="cp-sspin-show" class="cp-btn cp-navy-btn">Show PIN</button>
                    <button id="cp-sspin-generate" class="cp-btn cp-teal-btn">Generate New PIN</button>
                </div>

                <label class="cp-sec-label" style="margin-top:1rem;">Enter new PIN</label>
                <input type="text" maxlength="6" class="cp-input" id="cp-sspin-input" placeholder="123456">

                <button class="cp-btn cp-teal-btn" style="margin-top:1rem;">Save SSPIN</button>
            </div>

        </div>

        <footer class="cp-modal-footer">
            <button class="cp-btn cp-small-btn cp-navy-btn cp-password-close">Close</button>
        </footer>

    </div>
</div>
