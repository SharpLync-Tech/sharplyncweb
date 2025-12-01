{{-- 
  Partial: customers/portal/modals/security-modal.blade.php
  Original 2FA modal — unchanged 
--}}

<div id="cp-security-modal" class="cp-modal-backdrop" aria-hidden="true">
    <div class="cp-modal-sheet">

        <header class="cp-modal-header">
            <div>
                <h3 id="cpSecurityTitle">Security & Login Protection</h3>
                <p class="cp-modal-subtitle">
                    Manage how you protect access to your SharpLync customer portal.
                </p>
            </div>
            <button class="cp-modal-close">&times;</button>
        </header>

        <div class="cp-modal-body">

            {{-- SCREEN 1 --}}
            <div id="cp-modal-screen-main">

                {{-- EMAIL 2FA --}}
                <div class="cp-sec-card cp-sec-bordered">
                    <div class="cp-sec-card-header">
                        <div class="cp-sec-title-row">
                            <span class="cp-sec-icon">
                                <svg viewBox="0 0 24 24" class="cp-icon-svg">
                                    <path d="M20 4H4a2 2 0 0 0-2 2v12a2 
                                             2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 
                                             2 0 0 0-2-2zm0 4-8 5-8-5V6l8 
                                             5 8-5v2z"/>
                                </svg>
                            </span>
                            <div>
                                <h4>Email Authentication</h4>
                                <p class="cp-sec-desc">
                                    Receive a one-time security code via email when signing in.
                                </p>
                            </div>
                        </div>

                        <label class="cp-switch">
                            <input id="cp-toggle-email"
                                   type="checkbox"
                                   data-setting="email"
                                   @if($u->two_factor_email_enabled) checked @endif
                                   data-persist-on="{{ $u->two_factor_email_enabled ? '1' : '' }}">
                            <span class="cp-slider cp-slider-teal"></span>
                        </label>
                    </div>
                </div>

                {{-- AUTHENTICATOR APP --}}
                <div class="cp-sec-card cp-sec-bordered">
                    <div class="cp-sec-card-header">
                        <div class="cp-sec-title-row">
                            <span class="cp-sec-icon">
                                <svg viewBox="0 0 24 24" class="cp-icon-svg">
                                    <path d="M12 2a5 5 0 0 0-5 5v3H6c-1.1 0-2 .9-2 
                                             2v8c0 1.1.9 2 2 
                                             2h12c1.1 0 2-.9 
                                             2-2v-8c0-1.1-.9-2-2-2h-1V7a5 
                                             5 0 0 0-5-5zm-3 
                                             5a3 3 0 0 1 6 0v3H9V7z"/>
                                </svg>
                            </span>
                            <div>
                                <h4>Authenticator App</h4>
                                <p class="cp-sec-desc">
                                    Use a 6-digit code from Google Authenticator or another TOTP app.
                                </p>
                            </div>
                        </div>

                        <label class="cp-switch">
                            <input id="cp-toggle-auth"
                                   type="checkbox"
                                   data-setting="auth"
                                   @if($u->two_factor_app_enabled) checked @endif
                                   data-persist-on="{{ $u->two_factor_app_enabled ? '1' : '' }}">
                            <span class="cp-slider cp-slider-teal"></span>
                        </label>
                    </div>
                </div>

                {{-- SMS DISABLED --}}
                <div class="cp-sec-card cp-sec-bordered cp-sec-disabled">
                    <div class="cp-sec-card-header">
                        <div class="cp-sec-title-row">
                            <span class="cp-sec-icon">
                                <svg viewBox="0 0 24 24" class="cp-icon-svg">
                                    <path d="M17 1H7C5.34 1 4 2.34 4 
                                             4v16c0 1.66 1.34 3 3 
                                             3h10c1.66 0 3-1.34 
                                             3-3V4c0-1.66-1.34-3-3-3zm0 
                                             18H7V5h10v14z"/>
                                </svg>
                            </span>
                            <div><h4>SMS Verification</h4></div>
                        </div>

                        <label class="cp-switch disabled">
                            <input id="cp-toggle-sms" type="checkbox" disabled>
                            <span class="cp-slider"></span>
                        </label>
                    </div>
                </div>

            </div>

            {{-- EMAIL SETUP --}}
            <div id="cp-modal-screen-email-setup" style="display:none;">

                <div class="cp-sec-card cp-sec-bordered">
                    <div class="cp-sec-card-header">
                        <div class="cp-sec-title-row">
                            <span class="cp-sec-icon">
                                <svg viewBox="0 0 24 24" class="cp-icon-svg">
                                    <path d="M20 4H4a2 2 0 0 0-2 2v12a2 
                                             2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 
                                             2 0 0 0-2-2zm0 4-8 5-8-5V6l8 
                                             5 8-5v2z"/>
                                </svg>
                            </span>
                            <div>
                                <h4>Set Up Email Authentication</h4>
                                <p class="cp-sec-desc">We’ll send a verification code to your email.</p>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:1rem;">
                        <p class="cp-sec-desc">Your email on file:</p>
                        <p style="font-weight:600; margin:0 0 .75rem;">{{ $maskedEmail }}</p>

                        <p id="cp-email-status" class="cp-sec-desc" style="display:none;"></p>

                        <div id="cp-email-send-block" style="margin-top:1rem;">
                            <button id="cp-email-setup-send" class="cp-btn cp-teal-btn">
                                Send Verification Code
                            </button>
                        </div>

                        <div id="cp-email-verify-block" style="display:none; margin-top:1.25rem;">

                            <p class="cp-sec-desc">Enter the 6-digit code:</p>

                            <div id="cp-email-otp-row"
                                 style="display:flex; gap:.45rem; margin:.75rem 0;">
                                @for($i=0;$i<6;$i++)
                                    <input type="text" maxlength="1" inputmode="numeric"
                                           class="cp-otp-input"
                                           style="width:2.3rem; height:2.7rem;
                                                  text-align:center; font-size:1.4rem;">
                                @endfor
                            </div>

                            <button id="cp-email-setup-verify" class="cp-btn cp-teal-btn">
                                Verify & Enable
                            </button>

                            <button id="cp-email-setup-resend"
                                    class="cp-btn cp-small-btn cp-navy-btn"
                                    style="margin-left:.5rem;">
                                Resend Code
                            </button>

                            <p id="cp-email-error"
                               style="display:none; margin-top:.75rem; color:#b3261e;">
                                Invalid or expired code.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- AUTH SETUP --}}
            <div id="cp-modal-screen-auth-setup" style="display:none;">

                <div class="cp-sec-card cp-sec-bordered">
                    <div class="cp-sec-card-header">
                        <div class="cp-sec-title-row">
                            <span class="cp-sec-icon">
                                <svg viewBox="0 0 24 24" class="cp-icon-svg">
                                    <path d="M12 2a5 5 0 0 0-5 5v3H6c-1.1 0-2 .9-2 
                                             2v8c0 1.1.9 2 2 
                                             2h12c1.1 0 2-.9 
                                             2-2v-8c0-1.1-.9-2-2-2h-1V7a5 
                                             5 0 0 0-5-5zm-3 
                                             5a3 3 0 0 1 6 0v3H9V7z"/>
                                </svg>
                            </span>
                            <div>
                                <h4>Set Up Authenticator App</h4>
                                <p class="cp-sec-desc">Scan the QR code then enter the 6-digit code.</p>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:1rem;">

                        <div style="display:flex; flex-direction:column; align-items:center; gap:1rem;">
                            <div id="cp-auth-qr-wrapper" style="display:none;">
                                <img id="cp-auth-qr" src="" alt="Authenticator QR Code"
                                     style="width:180px; height:180px; border-radius:12px; background:#fff; padding:8px;">
                            </div>

                            <div id="cp-auth-secret-block" style="display:none; text-align:center;">
                                <p class="cp-sec-desc" style="margin-bottom:0.25rem;">Manual code:</p>
                                <code id="cp-auth-secret" style="font-weight:700; letter-spacing:0.12em;"></code>
                            </div>

                            <button id="cp-auth-start" class="cp-btn cp-teal-btn">
                                Generate QR Code
                            </button>
                        </div>

                        <div id="cp-auth-verify-block" style="display:none; margin-top:1.5rem;">
                            <p class="cp-sec-desc">Enter the 6-digit code:</p>

                            <input id="cp-auth-code"
                                   type="text"
                                   maxlength="6"
                                   inputmode="numeric"
                                   style="width:180px; height:2.7rem; text-align:center;
                                          font-size:1.4rem; letter-spacing:0.4em;">

                            <button id="cp-auth-verify" class="cp-btn cp-teal-btn" style="margin-top:1rem;">
                                Verify & Enable
                            </button>

                            <p id="cp-auth-status"
                               class="cp-sec-desc"
                               style="display:none; margin-top:.75rem;"></p>

                            <p id="cp-auth-error"
                               style="display:none; margin-top:.75rem; color:#b3261e;">
                            </p>
                        </div>

                        @if($u->two_factor_app_enabled)
                            <div style="margin-top:1.5rem; border-top:1px solid #e0e0e0; padding-top:1rem;">
                                <p class="cp-sec-desc">
                                    Authenticator app is currently <strong>enabled</strong>.
                                </p>
                                <button id="cp-auth-disable" class="cp-btn cp-navy-btn">
                                    Disable Authenticator App
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        </div>

        <footer class="cp-modal-footer">
            <button id="cp-email-setup-back"
                    class="cp-btn cp-small-btn cp-navy-btn"
                    style="display:none; margin-right:.5rem;">
                Back
            </button>

            <button id="cp-auth-setup-back"
                    class="cp-btn cp-small-btn cp-navy-btn"
                    style="display:none; margin-right:.5rem;">
                Back
            </button>

            <button class="cp-btn cp-small-btn cp-navy-btn cp-modal-close-btn">
                Close
            </button>
        </footer>

    </div>
</div>
