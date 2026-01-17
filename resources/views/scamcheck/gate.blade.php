@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/scamchecker.css') }}">
@endpush

@section('title', 'SharpLync ThreatCheck – Important Notice')

@section('content')
<div class="sc-page">
    <div style="max-width:900px; margin:0 auto; padding-top:40px;">

        <h2 class="threat-title">
            <img src="/images/security.png" class="shield-icon-img" alt="SharpLync Security Shield">
            SharpLync <strong>ThreatCheck</strong>
        </h2>

        <div class="result-box safe" style="border-left-width:6px;">

            <h3 class="gate-title" style="margin-top:0;">
                Before you continue
            </h3>

            <p>
                <strong>SharpLync ThreatCheck</strong> is an <strong>informational analysis tool</strong> designed to help
                identify potential scam indicators in emails, messages, or uploaded files.
            </p>

            <p>
                Results are <strong>not guaranteed to be accurate</strong> and should never be the sole basis for action.
            </p>

            <p>
                You are responsible for <strong>independently verifying</strong> any communication before responding.
            </p>

            <div class="section-title">Please do not submit:</div>
            <ul class="red-flag-list">
                <li>Passwords, authentication codes, or private credentials</li>
                <li>Highly sensitive personal information</li>
                <li>Confidential or legally protected business data</li>
            </ul>

            <p style="margin-top:16px;">
                Content is processed for <strong>analysis only</strong> and is
                <strong>not stored long-term</strong>.
            </p>

            <form method="POST" action="/scam-checker/acknowledge" style="margin-top:24px;">
                @csrf

                <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <input type="checkbox" id="acknowledge"
                           style="width:18px; height:18px;">
                    <span>I understand and wish to continue</span>
                </label>

                <div style="margin-top:30px;">
                    <button
                        type="submit"
                        id="proceed-btn"
                        class="scam-btn"
                        disabled
                        style="opacity:0.5;">
                        Proceed to ThreatCheck
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkbox = document.getElementById('acknowledge');
    const button   = document.getElementById('proceed-btn');

    if (!checkbox || !button) return;

    checkbox.addEventListener('change', function () {
        button.disabled = !this.checked;
        button.style.opacity = this.checked ? '1' : '0.5';
    });
});
</script>
@endpush
