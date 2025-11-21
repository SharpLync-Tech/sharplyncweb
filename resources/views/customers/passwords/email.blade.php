@extends('layouts.base')

@section('title', 'Forgot Password')

@section('content')
<section class="hero" style="padding-top: 4rem;">
    <div class="hero-cpu-bg">
        <img src="{{ asset('images/hero-cpu.png') }}" alt="">
    </div>

    <img src="{{ asset('images/sharplync-logo.png') }}" class="hero-logo" alt="SharpLync Logo">

    <div class="hero-cards fade-section" style="justify-content: center;">
        <div class="tile transparent single-reg-card"
             style="width: 500px; max-width: 90%; padding: 2.5rem;
                    background: rgba(10,42,77,0.85); backdrop-filter: blur(6px);
                    border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.25);">

            <h2 style="color: white; text-align: center; margin-bottom: 1rem;">
                Reset Your Password
            </h2>

            <p style="color: rgba(255,255,255,0.8); text-align:center; margin-bottom: 1.5rem;">
                Enter your email and we’ll send you a reset link.
            </p>

            @if ($errors->any())
                <div style="color:#721c24; background-color:rgba(248,215,218,0.95);
                            border-radius:6px; padding:10px; margin-bottom:1rem;">
                    <ul style="margin:0; padding-left:18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('customer.password.email') }}">
                @csrf

                <div style="margin-bottom: 1rem;">
                    <label for="email" style="color:white; font-weight:600;">Email Address</label>
                    <input type="email" id="email" name="email" required
                           style="width:100%; padding:0.6rem; border-radius:6px;
                                  color:white; background:rgba(255,255,255,0.08);
                                  border:1px solid rgba(255,255,255,0.25);">
                </div>

                <button type="submit"
                        style="width:100%; padding:0.75rem; background:#2CBFAE;
                               border-radius:8px; color:white; font-weight:600;">
                    Send Reset Link
                </button>

                <div style="text-align:center; margin-top:1rem;">
                    <a href="{{ route('customer.login') }}"
                       style="color:#2CBFAE; text-decoration:none;">
                        Back to login
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
