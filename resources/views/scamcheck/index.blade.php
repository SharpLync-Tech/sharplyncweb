@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/scamchecker.css') }}">
@endpush

@section('title', 'SharpLync ThreatCheck')

@section('content')
<div class="sc-page">
<div style="max-width:1100px; margin:0 auto; padding-top:40px;">

    <!-- THREATCHHECK HEADER -->
        <h2 class="threat-title">
        <img src="/images/security.png" class="shield-icon-img" alt="SharpLync Security Shield">
        SharpLync <strong>ThreatCheck</strong>
    </h2>



    <!-- FORM AREA (hidden after scan automatically) -->
    @if(!isset($result))
    <div id="form-area">
        <form id="scam-form" method="POST" action="/scam-checker" enctype="multipart/form-data">
            @csrf        

            <textarea name="message" rows="10" placeholder="Paste text OR upload an email (.eml/.msg/.txt):">
@if(isset($input)){{ $input }}@endif
            </textarea>

            <br><br>
            <input type="file" name="file">
            <br><br>

            <button type="submit" class="scam-btn" id="check-btn">Check Message</button>

            <!-- Clear button -->
            <button type="button" id="clear-btn" class="scam-btn outline" style="display:none;" onclick="clearScamForm()">Clear</button>
        </form>
    </div>
    @endif


    <!-- SCAN LOADER -->
    <div id="scan-loader" class="scan-loader" style="display:none;">
            <div class="tc-wheel-loader">
                <div class="tc-seg"></div>
                <div class="tc-seg"></div>
                <div class="tc-seg"></div>
                <div class="tc-seg"></div>
                <div class="tc-seg"></div>
                <div class="tc-seg"></div>
                <div class="tc-seg"></div>
                <div class="tc-seg"></div>
                <div class="tc-seg"></div>
                <div class="tc-seg"></div>
                <div class="tc-seg"></div>
                <div class="tc-seg"></div>
            </div>

            <p class="scan-text">Scanning for threats…</p>
        </div>





    {{-- RESULTS --}}
    @if(isset($result))
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            document.getElementById('clear-btn').style.display = 'inline-block';
        });
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
    </div>

    {{-- AUTO SCROLL --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const el = document.querySelector('.result-container');
            if (el) {
                setTimeout(() => {
                    el.scrollIntoView({ behavior: "smooth", block: "start" });
                }, 250);
            }
        });
    </script>

    @endif

</div>
</div>
@endsection


<!-- PAGE SCRIPTS -->
<script>
// Intercept form submission
document.addEventListener("DOMContentLoaded", function () {

    const form     = document.getElementById("scam-form");
    const formArea = document.getElementById("form-area");
    const loader   = document.getElementById("scan-loader");

    if (form) {
        form.addEventListener("submit", function (e) {

            e.preventDefault(); // STOP instant submit
            formArea.classList.add("scanning");
            loader.style.display = "block";

            setTimeout(() => form.submit(), 250);
        });
    }
});


// Clear button
function clearScamForm() {
    document.querySelector('textarea[name="message"]').value = "";
    document.querySelector('input[type="file"]').value = null;

    const resultBox = document.querySelector('.result-container');
    if (resultBox) resultBox.remove();

    document.getElementById('form-area').classList.remove("scanning");
    document.getElementById('scan-loader').style.display = "none";
    document.getElementById('clear-btn').style.display = "none";
}
</script>
