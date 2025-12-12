@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/scamchecker.css') }}">
@endpush

@section('title', 'SharpLync ThreatCheck – Important Notice')

@section('content')
<div class="sc-page">
    <div style="max-width:900px; margin:0 auto; padding-top:40px;">

        <!-- HEADER -->
        <h2 class="threat-title">
            <img src="/images/security.png" class="shield-icon-img" alt="SharpLync Security Shield">
            SharpLync <strong>ThreatCheck</strong>
        </h2>

        <!-- NOTICE CARD -->
        <div class="result-box safe" style="border-left-width:6px;">

            <h3 class="scam-result-title" style="margin-top:0;">
                Before you continue
            </h3>

            <p>
                SharpLync ThreatCheck is an <strong>informational analysis tool</strong> designed to help
                identify potential scam indicators in emails, messages, or uploaded files.
            </p>

            <p>
                While advanced analysis techniques are used, <strong>results are not guaranteed to be
                accurate</strong> and should not be relied upon as the sole basis for decision-making.
            </p>

            <p>
                You remain responsible for verifying the legitimacy of any communication before taking
                action.
            </p>

            <div class="section-title">Please do not submit:</div>
            <ul class="red-flag-list">
                <li>Passwords, authentication codes, or private credentials</li>
                <li>Highly sensitive personal information</li>
                <li>Confidential or legally protected business data</li>
            </ul>

            <p style="margin-top:16px;">
                Submitted content is processed for analysis only and is <strong>not stored long-term</strong>.
            </p>

            <!-- ACKNOWLEDGEMENT -->
            <div style="margin-top:24px;">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <input type="checkbox" id="acknowledge"
                           style="width:18px; height:18px;">
                    <span>I understand and wish to continue</span>
                </label>
            </div>

            <!-- ACTION -->
            <div style="margin-top:30px;">
                <a href="/scam-checker"
                   id="proceed-btn"
                   class="scam-btn"
                   style="pointer-events:none; opacity:0.5;">
                    Proceed to ThreatCheck
                </a>
            </div>

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
        if (this.checked) {
            button.style.pointerEvents = 'auto';
            button.style.opacity = '1';
        } else {
            button.style.pointerEvents = 'none';
            button.style.opacity = '0.5';
        }
    });
});
</script>
@endpush