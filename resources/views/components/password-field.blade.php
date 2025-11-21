<?php
// Blade component for reusable password field
?>
<!-- resources/views/components/password-field.blade.php -->
@props([
    'label' => 'Password',
    'name' => 'password',
    'confirm' => null,
    'showGenerator' => true,
    'showStrength' => true,
])

<div class="sl-password-wrapper" style="margin-bottom:1.5rem; color:white;">
    <label style="font-weight:600; color:white;">{{ $label }}</label>

    <div style="position:relative;">
        <input type="password" name="{{ $name }}" id="{{ $name }}" required
            style="width:100%; padding:0.6rem 2.5rem 0.6rem 0.6rem; border-radius:6px;
                   background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.3);
                   color:white;">

        <span class="sl-toggle-password" data-target="{{ $name }}"
            style="position:absolute; right:10px; top:50%; transform:translateY(-50%);
                   cursor:pointer; font-size:18px;">
            <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                 fill="none" stroke="#2CBFAE" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round">
              <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
            <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                 fill="none" stroke="#2CBFAE" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" style="display:none;">
              <path d="M17.94 17.94A10.94 10.94 0 0112 19c-7 0-11-7-11-7
                       1.18-2.07 2.68-3.94 4.44-5.5"/>
              <path d="M9.88 9.88A3 3 0 0114.12 14.12"/>
              <line x1="1" y1="1" x2="23" y2="23"/>
            </svg>
        </span>
    </div>

    @if($showStrength)
    <div style="margin-top:8px; height:8px; background:rgba(255,255,255,0.15); border-radius:6px;">
        <div id="{{ $name }}-strength-bar" style="height:100%; width:0%; border-radius:6px;
            transition:width 0.25s;"></div>
    </div>
    <div id="{{ $name }}-strength-text" style="margin-top:6px; font-size:0.9rem; color:#ccc;">
        Enter a password
    </div>
    @endif

    @if($showGenerator)
    <button type="button" class="sl-generate" data-target="{{ $name }}"
        style="margin-top:8px; background:#104976; color:white; padding:0.45rem 0.7rem;
               border:none; border-radius:6px; font-size:0.85rem; cursor:pointer;">
        Generate Strong Password
    </button>
    @endif

    @if($confirm)
    <div style="margin-top:1rem;">
        <label style="font-weight:600; color:white;">Confirm Password</label>
        <input type="password" name="{{ $confirm }}" id="{{ $confirm }}" required
               style="width:100%; padding:0.6rem; border-radius:6px;
                      background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.3);
                      color:white;">
        <div id="{{ $confirm }}-match-text" style="margin-top:6px; font-size:0.9rem;"></div>
    </div>
    @endif
</div>

<!-- Component Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Toggle Password Visibility
    document.querySelectorAll('.sl-toggle-password').forEach(toggler => {
        toggler.addEventListener('click', function () {
            let target = document.getElementById(this.dataset.target);
            let open = this.querySelector('.eye-open');
            let closed = this.querySelector('.eye-closed');

            if (target.type === "password") {
                target.type = "text";
                open.style.display = "none";
                closed.style.display = "block";
            } else {
                target.type = "password";
                open.style.display = "block";
                closed.style.display = "none";
            }
        });
    });

    // Strength Meter
    document.querySelectorAll('input[type=password]').forEach(input => {
        input.addEventListener('input', function () {
            let name = this.id;
            let bar = document.getElementById(`${name}-strength-bar`);
            let text = document.getElementById(`${name}-strength-text`);
            if (!bar || !text) return;

            const val = this.value;
            let strength = 0;
            if (val.length >= 8) strength++;
            if (/[A-Z]/.test(val)) strength++;
            if (/[0-9]/.test(val)) strength++;
            if (/[^A-Za-z0-9]/.test(val)) strength++;

            let width = (strength / 4) * 100;
            bar.style.width = width + "%";

            if (strength <= 1) { bar.style.background = "#ff4d4d"; text.textContent = "Weak"; }
            else if (strength == 2) { bar.style.background = "#ffcc00"; text.textContent = "Okay"; }
            else if (strength == 3) { bar.style.background = "#2CBFAE"; text.textContent = "Strong"; }
            else { bar.style.background = "#2CBFAE"; text.textContent = "Very Strong"; }
        });
    });

    // Password Match Checker
    document.querySelectorAll('input[type=password]').forEach(input => {
        if (input.id.includes('confirmation')) {
            input.addEventListener('input', function () {
                let pw = document.getElementById(this.id.replace('_confirmation', '')).value;
                let matchText = document.getElementById(`${this.id}-match-text`);

                if (!pw || !this.value) { matchText.textContent = ''; return; }

                if (pw === this.value) {
                    matchText.style.color = "#2CBFAE";
                    matchText.textContent = "✔ Passwords match";
                } else {
                    matchText.style.color = "#ff4d4d";
                    matchText.textContent = "✖ Passwords do not match";
                }
            });
        }
    });

    // Generate Password Button
    document.querySelectorAll('.sl-generate').forEach(button => {
        button.addEventListener('click', function () {
            const chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%&*?";
            let pass = "";
            for (let i = 0; i < 14; i++) {
                pass += chars.charAt(Math.floor(Math.random() * chars.length));
            }

            const field = document.getElementById(this.dataset.target);
            field.value = pass;
            field.dispatchEvent(new Event('input'));

            const confirm = document.getElementById(`${this.dataset.target}_confirmation`);
            if (confirm) {
                confirm.value = pass;
                confirm.dispatchEvent(new Event('input'));
            }
        });
    });
});
</script>
