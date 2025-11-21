@extends('layouts.base')

@section('title', 'Reset Password')

@section('content')
<section class="hero" style="padding-top: 4rem;">
    <div class="hero-cpu-bg">
        <img src="{{ asset('images/hero-cpu.png') }}" alt="">
    </div>

    <img src="{{ asset('images/sharplync-logo.png') }}" class="hero-logo">

    <div class="hero-cards fade-section" style="justify-content: center;">
        <div class="tile transparent single-reg-card"
             style="width: 500px; max-width: 90%; padding: 2.5rem;
                    background: rgba(10,42,77,0.85); border-radius: 16px;
                    backdrop-filter: blur(6px); box-shadow: 0 8px 24px rgba(0,0,0,0.25);">

            <h2 style="color:white; text-align:center; margin-bottom:1rem;">
                Choose a New Password
            </h2>

            @if (session('error'))
                <div style="color:#721c24; background-color:rgba(248,215,218,0.95);
                            border-radius:6px; padding:12px; margin-bottom: 1rem;">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div style="color:#721c24; background-color:rgba(248,215,218,0.95);
                            border-radius:6px; padding:12px; margin-bottom: 1rem;">
                    <ul style="margin:0; padding-left:18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('customer.password.update') }}" method="POST">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <!-- NEW PASSWORD INPUT + STRENGTH METER -->
                <div style="margin-bottom:1rem;">
                    <label style="color:white; font-weight:600;">New Password</label>
                    <input type="password" name="password" id="passwordInput" required
                           style="width:100%; padding:0.6rem; border-radius:6px;
                                  color:white; background:rgba(255,255,255,0.08);
                                  border:1px solid rgba(255,255,255,0.25);">
                    
                    <!-- Strength bar -->
                    <div style="margin-top:8px; height:8px; width:100%; background:rgba(255,255,255,0.15); border-radius:6px;">
                        <div id="passwordStrengthBar" style="
                            height:100%; width:0%; border-radius:6px;
                            transition:width 0.2s ease-in-out;
                        "></div>
                    </div>

                    <!-- Strength label -->
                    <div id="passwordStrengthText" style="margin-top:6px; font-size:0.9rem; color:#ccc;"></div>
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label style="color:white; font-weight:600;">Confirm Password</label>
                    <input type="password" name="password_confirmation" required
                           style="width:100%; padding:0.6rem; border-radius:6px;
                                  color:white; background:rgba(255,255,255,0.08);
                                  border:1px solid rgba(255,255,255,0.25);">
                </div>

                <button type="submit"
                        style="width:100%; padding:0.75rem; background:#2CBFAE;
                               border-radius:8px; color:white; font-weight:600;">
                    Reset Password
                </button>

                <div style="text-align:center; margin-top:1rem;">
                    <a href="{{ route('customer.login') }}" style="color:#2CBFAE;">
                        Back to login
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Password Strength Script -->
<script>
document.getElementById('passwordInput').addEventListener('input', function() {
    const val = this.value;
    const bar = document.getElementById('passwordStrengthBar');
    const text = document.getElementById('passwordStrengthText');

    let strength = 0;

    if (val.length >= 8) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;

    let width = (strength / 4) * 100;
    bar.style.width = width + "%";

    if (strength <= 1) {
        bar.style.background = "#ff4d4d"; // red
        text.textContent = "Weak";
    } else if (strength === 2) {
        bar.style.background = "#ffcc00"; // yellow
        text.textContent = "Okay";
    } else if (strength === 3) {
        bar.style.background = "#2CBFAE"; // teal-ish strong
        text.textContent = "Strong";
    } else {
        bar.style.background = "#2CBFAE"; // strongest teal
        text.textContent = "Very Strong";
    }
});
</script>

@endsection
