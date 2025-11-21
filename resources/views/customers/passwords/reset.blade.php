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

                <!-- 🔥 New reusable SharpLync password component -->
                <x-password-field 
                    label="New Password"
                    name="password"
                    confirm="password_confirmation"
                    show-generator="true"
                    show-strength="true"
                />

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
@endsection
