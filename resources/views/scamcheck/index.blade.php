@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/scamchecker.css') }}">
@endpush

@section('title', 'SharpLync ThreatCheck')

@section('content')
<div class="sc-page">
    <div style="max-width:1100px; margin:0 auto; padding-top:40px;">

        <!-- HEADER -->
        <h2 class="threat-title">
            <img src="/images/security.png" class="shield-icon-img" alt="SharpLync Security Shield">
            SharpLync <strong>ThreatCheck</strong>
        </h2>

        <!-- 📊 STATS BAR -->
        @if(isset($stats))
        <div class="threat-stats">
            <div class="stat-item">
                <span class="stat-number">{{ number_format($stats->total_checked ?? 0) }}</span>
                <span class="stat-label">Total Checked</span>
            </div>
            <div class="stat-item safe">
                <span class="stat-number">{{ number_format($stats->total_safe ?? 0) }}</span>
                <span class="stat-label">Safe</span>
            </div>
            <div class="stat-item scam">
                <span class="stat-number">{{ number_format($stats->total_scam ?? 0) }}</span>
                <span class="stat-label">Scam</span>
            </div>
            <div class="stat-item unknown">
                <span class="stat-number">{{ number_format($stats->total_unknown ?? 0) }}</span>
                <span class="stat-label">Unknown</span>
            </div>
        </div>

        <p class="stats-footnote">
            Since December 2025 · Anonymous scans · No content stored
        </p>
        @endif

        {{-- FORM --}}
        @if(!isset($result))
        <div id="form-area">
            <form id="scam-form" method="POST" action="/scam-checker" enctype="multipart/form-data">
                @csrf

                <textarea
                    id="scam-text"
                    name="message"
                    rows="10"
                    placeholder="1. Copy and paste your email or message text
2. Upload a saved email (.eml recommended, .msg supported)
3. Upload or drag & drop a screenshot of the message (JPG or PNG)
4. Paste SMS, WhatsApp, or other message text"
                >{{ $input ?? '' }}</textarea>

                <div id="attached-file" class="attached-file" style="display:none;">
                    <span class="file-icon">🛡️ File ready:</span>
                    <span class="file-name"></span>
                    <button type="button" class="remove-file" title="Remove file">✕</button>
                </div>

                <br><br>

                <input type="file" name="file" id="file-input" hidden>

                <button type="button"
                        class="file-browse-btn"
                        onclick="document.getElementById('file-input').click()">
                    Browse…
                </button>

                <p style="margin-top:8px; font-size:0.9em; color:#ffffff; opacity:0.85;">
                    📎 Supports email files (.eml, .msg) and screenshots (JPG, PNG)
                </p>

                <br>

                <button type="submit" class="scam-btn" id="check-btn">
                    Check Message
                </button>
            </form>
        </div>
        @endif

        <!-- SCAN LOADER -->
        <div id="scan-loader" class="scan-loader" style="display:none;">
            <div class="scan-center">
                <div class="tc-wheel">
                    <span></span><span></span><span></span><span></span>
                    <span></span><span></span><span></span><span></span>
                    <span></span><span></span><span></span><span></span>
                </div>
                <p class="scan-text">Scanning for threats…</p>
            </div>
        </div>

        {{-- RESULTS --}}
        @if(isset($result))

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const result = document.querySelector('.result-container');
                if (result) {
                    setTimeout(() => {
                        result.scrollIntoView({ behavior: "smooth", block: "start" });
                    }, 300);
                }
            });

            function startNewCheck() {
                window.location.href = '/scam-checker';
            }
        </script>

        <div class="result-container" style="margin-top:40px;">
            <h3 class="scam-result-title">Scam Analysis Result</h3>

            @php
                $json = json_decode($result, true);
                $isJson = json_last_error() === JSON_ERROR_NONE && is_array($json);

                if ($isJson) {
                    $verdict     = ucfirst($json['verdict'] ?? '');
                    $score       = $json['risk_score'] ?? 'N/A';
                    $summary     = $json['summary'] ?? '';
                    $redFlags    = $json['red_flags'] ?? [];
                    $recommended = $json['recommended_action'] ?? '';

                    if (is_numeric($score)) {
                        $severityClass =
                            $score >= 70 ? 'danger' :
                            ($score >= 40 ? 'sus' : 'safe');
                    } else {
                        $v = strtolower($verdict);
                        $severityClass =
                            str_contains($v,'scam') ? 'danger' :
                            (str_contains($v,'suspicious') || str_contains($v,'unclear') ? 'sus' : 'safe');
                    }
                }
            @endphp

            <div class="result-box {{ $severityClass }}">
                <p>
                    <span class="value">Verdict:</span>
                    <span class="verdict-text">{{ $verdict }}</span>
                    <span class="verdict-dot {{ $severityClass }}"></span>
                </p>

                <p><span class="value">Risk Score:</span> {{ $score }}</p>

                <div class="section-title">Summary</div>
                <p>{!! nl2br(e($summary)) !!}</p>

                <div class="section-title">Red Flags</div>
                <ul class="red-flag-list">
                    @foreach($redFlags as $flag)
                        <li>{{ $flag }}</li>
                    @endforeach
                </ul>

                <div class="section-title">Recommended Action</div>
                <p>{!! nl2br(e($recommended)) !!}</p>
            </div>

            <!-- IMPORTANT NOTICE -->
            <div class="result-box safe"
                 style="margin-top:24px; border-left-width:6px; color:#0a2a4d;">
                <strong>Important notice</strong>
                <p style="margin-top:8px;">
                    SharpLync ThreatCheck provides an <strong>informational analysis only</strong>.
                    Results are <strong>not guaranteed to be accurate or complete</strong>.
                </p>
                <p>
                    Always <strong>independently verify</strong> messages before clicking links,
                    making payments, or sharing information.
                </p>
                <p style="margin-bottom:0;">
                    SharpLync accepts <strong>no liability for any loss or damage</strong>
                    resulting from reliance on this analysis.
                </p>
            </div>

            <!-- ACTIONS -->
            <div style="margin-top:30px; display:flex; gap:16px; flex-wrap:wrap;">
                <button type="button"
                        class="scam-btn"
                        onclick="startNewCheck()">
                    Check another message
                </button>

                <a href="/" class="scam-btn outline">
                    Back to SharpLync
                </a>
            </div>
        </div>
        @endif

    </div>

    <!-- DRAG & DROP OVERLAY -->
    <div id="drop-overlay">
        <div class="drop-box">
            <img src="/images/security.png" alt="Security Shield">
            <p>Drop file to scan</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/scamcheck/scan.js') }}" defer></script>
<script src="{{ asset('js/scamcheck/dragdrop.js') }}" defer></script>
<script src="{{ asset('js/scamcheck/index.js') }}" defer></script>
@endpush
