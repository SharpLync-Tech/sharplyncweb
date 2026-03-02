<!-- Marketing Page: Campaign Edit -->
@extends('marketing.admin.layout')

@section('content')
@php
    $brandScope = $brandScope ?? 'both';
@endphp

<h1 style="margin-bottom:20px;">Edit Campaign</h1>

@if ($errors->any())
    <div style="background:#ffecec;border:1px solid #ffb3b3;padding:12px;border-radius:8px;margin-bottom:20px;">
        <strong>Validation failed:</strong>
        <ul style="margin:10px 0 0 18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('error'))
    <div style="background:#ffecec;border:1px solid #ffb3b3;padding:12px;border-radius:8px;margin-bottom:20px;">
        <strong>Error:</strong> {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div style="background:#e6f7ef;border:1px solid #b7e2c9;padding:12px;border-radius:8px;margin-bottom:20px;">
        <strong>Success:</strong> {{ session('success') }}
    </div>
@endif

<form method="POST" action="{{ route('marketing.admin.campaigns.update', $campaign->id) }}">
    @csrf

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if($brandScope === 'both')
        <div style="margin-bottom:20px;">
            <label style="display:block;margin-bottom:6px;font-weight:600;">Brand</label>
            <select name="brand" required style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
                <option value="sl" {{ old('brand', $campaign->brand) === 'sl' ? 'selected' : '' }}>SharpLync</option>
                <option value="sf" {{ old('brand', $campaign->brand) === 'sf' ? 'selected' : '' }}>SharpFleet</option>
            </select>
        </div>
    @else
        <input type="hidden" name="brand" value="{{ $campaign->brand }}">
    @endif

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Campaign Name (internal)</label>
        <input type="text" name="name" required value="{{ old('name', $campaign->name) }}"
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
    </div>

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Email Subject</label>
        <input type="text" name="subject" required value="{{ old('subject', $campaign->subject) }}"
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
    </div>

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Preheader (optional)</label>
        <input type="text" name="preheader" value="{{ old('preheader', $campaign->preheader) }}"
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
        <div style="font-size:12px;color:#666;margin-top:6px;">
            Short preview text shown next to the subject in most inboxes.
        </div>
    </div>

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">CTA Text (optional)</label>
        <input type="text" name="cta_text" value="{{ old('cta_text', $campaign->cta_text) }}"
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
    </div>

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">CTA URL (optional)</label>
        <input type="text" name="cta_url" value="{{ old('cta_url', $campaign->cta_url) }}"
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
    </div>

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Template</label>
        <select name="template_view" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
            <option value="" {{ old('template_view', $campaign->template_view) === null ? 'selected' : '' }}>Default (brand template)</option>
            <option value="emails.marketing.templates.sl-basic" {{ old('template_view', $campaign->template_view) === 'emails.marketing.templates.sl-basic' ? 'selected' : '' }}>SharpLync - Basic</option>
            <option value="emails.marketing.templates.sf-basic" {{ old('template_view', $campaign->template_view) === 'emails.marketing.templates.sf-basic' ? 'selected' : '' }}>SharpFleet - Basic</option>
        </select>
    </div>

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Hero Image URL (optional)</label>
        <input type="text" name="hero_image" value="{{ old('hero_image', $campaign->hero_image) }}"
               style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;">
    </div>

    <div style="margin-bottom:20px;">
        <label style="display:block;margin-bottom:6px;font-weight:600;">Email Content</label>
        <div style="background:#f8f9fb;border:1px solid #e5e7eb;border-radius:6px;padding:12px;margin-bottom:12px;">
            <div style="font-weight:600;margin-bottom:8px;">Generate with AI</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label style="display:block;font-size:12px;color:#666;margin-bottom:4px;">Goal</label>
                    <input type="text" id="ai-goal" placeholder="Promote a new feature, announce a promo, etc."
                           style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;">
                </div>
                <div>
                    <label style="display:block;font-size:12px;color:#666;margin-bottom:4px;">Audience</label>
                    <input type="text" id="ai-audience" placeholder="Existing customers, leads, fleet managers, etc."
                           style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;">
                </div>
                <div>
                    <label style="display:block;font-size:12px;color:#666;margin-bottom:4px;">Tone</label>
                    <select id="ai-tone" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;">
                        <option value="professional">Professional</option>
                        <option value="friendly">Friendly</option>
                        <option value="concise">Concise</option>
                        <option value="energetic">Energetic</option>
                        <option value="formal">Formal</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;color:#666;margin-bottom:4px;">Detail Level</label>
                    <select id="ai-fluff" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;">
                        <option value="none">No Fluff (Concise)</option>
                        <option value="light">Light Fluff</option>
                        <option value="rich">More Fluff</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;color:#666;margin-bottom:4px;">CTA Text</label>
                    <input type="text" id="ai-cta-text" placeholder="Book a demo"
                           style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;">
                </div>
                <div>
                    <label style="display:block;font-size:12px;color:#666;margin-bottom:4px;">CTA URL</label>
                    <input type="text" id="ai-cta-url" placeholder="https://example.com"
                           style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;">
                </div>
                <div style="grid-column:1 / -1;">
                    <label style="display:block;font-size:12px;color:#666;margin-bottom:4px;">Key Points</label>
                    <textarea id="ai-key-points" rows="3" placeholder="Bullet points or key messages..."
                              style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;"></textarea>
                </div>
            </div>
            <div style="margin-top:10px;">
                <button type="button" id="ai-generate-btn" class="btn-send" style="background:#0ea5e9;">Generate & Replace</button>
                <span id="ai-status" style="margin-left:10px;font-size:12px;color:#666;"></span>
            </div>
        </div>
        <div id="marketing-quill-toolbar" class="quill-toolbar" style="margin-bottom:10px;">
            <span class="ql-formats">
                <select class="ql-header">
                    <option value="1">H1</option>
                    <option value="2">H2</option>
                    <option selected>Normal</option>
                </select>
            </span>
            <span class="ql-formats">
                <button class="ql-bold"></button>
                <button class="ql-italic"></button>
                <button class="ql-underline"></button>
            </span>
            <span class="ql-formats">
                <button class="ql-list" value="ordered"></button>
                <button class="ql-list" value="bullet"></button>
            </span>
            <span class="ql-formats">
                <button class="ql-link"></button>
                <button class="ql-image"></button>
            </span>
            <span class="ql-formats">
                <button class="ql-clean"></button>
            </span>
        </div>
        <div id="marketing-quill-editor" class="quill-editor" style="background:#fff;border:1px solid #ccc;border-radius:6px;min-height:220px;padding:10px;"></div>
        <input type="hidden" name="body_html" id="body_html" value="{{ old('body_html', $campaign->body_html) }}">
    </div>

    <button type="submit" class="btn-primary">
        Update Campaign
    </button>

    <a href="{{ route('marketing.admin.campaigns') }}" style="margin-left:12px;text-decoration:none;">
        Cancel
    </a>
</form>

<link href="{{ secure_asset('quill/quill.core.css') }}" rel="stylesheet">
<link href="{{ secure_asset('quill/quill.snow.css') }}" rel="stylesheet">
<script src="{{ secure_asset('quill/quill.min.js') }}"></script>
<script src="{{ secure_asset('js/marketing/marketing-quill.js') }}"></script>
<script>
    (function () {
        var storageKey = 'marketing_ai_{{ $campaign->id }}';
        var fields = ['ai-goal','ai-audience','ai-key-points','ai-tone','ai-fluff','ai-cta-text','ai-cta-url'];
        fields.forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            var saved = localStorage.getItem(storageKey + ':' + id);
            if (saved && !el.value) el.value = saved;
            el.addEventListener('input', function () {
                localStorage.setItem(storageKey + ':' + id, el.value || '');
            });
            el.addEventListener('change', function () {
                localStorage.setItem(storageKey + ':' + id, el.value || '');
            });
        });

        var btn = document.getElementById('ai-generate-btn');
        if (!btn) return;

        btn.addEventListener('click', function () {
            var statusEl = document.getElementById('ai-status');
            if (statusEl) statusEl.textContent = 'Generating...';

            var brandEl = document.querySelector('select[name="brand"]');
            var brand = brandEl ? brandEl.value : document.querySelector('input[name="brand"]').value;

            fetch("{{ route('marketing.admin.ai.generate') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    brand: brand,
                    goal: document.getElementById('ai-goal').value,
                    audience: document.getElementById('ai-audience').value,
                    key_points: document.getElementById('ai-key-points').value,
                    tone: document.getElementById('ai-tone').value,
                    fluff: document.getElementById('ai-fluff').value,
                    cta_text: document.getElementById('ai-cta-text').value,
                    cta_url: document.getElementById('ai-cta-url').value
                })
            })
            .then(function (res) {
                if (!res.ok) {
                    return res.text().then(function (text) {
                        throw new Error(text || ('HTTP ' + res.status));
                    });
                }
                return res.text().then(function (text) {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid JSON from server.');
                    }
                });
            })
            .then(function (data) {
                if (data.error) {
                    if (statusEl) statusEl.textContent = 'Error: ' + data.error;
                    return;
                }
                if (!data.subject && !data.preheader && !data.html) {
                    if (statusEl) statusEl.textContent = 'Error: AI returned no content.';
                    return;
                }
                var subject = document.querySelector('input[name="subject"]');
                var preheader = document.querySelector('input[name="preheader"]');
                var ctaText = document.querySelector('input[name="cta_text"]');
                var ctaUrl = document.querySelector('input[name="cta_url"]');
                if (subject && data.subject) subject.value = data.subject;
                if (preheader && data.preheader) preheader.value = data.preheader;
                if (ctaText && data.ctaText) ctaText.value = data.ctaText;
                if (ctaUrl && data.ctaUrl) ctaUrl.value = data.ctaUrl;
                if (window.MarketingQuill && data.html) {
                    var ok = window.MarketingQuill.setHtml(data.html);
                    if (!ok && statusEl) {
                        statusEl.textContent = 'Error: AI returned empty HTML.';
                        return;
                    }
                }
                if (statusEl) statusEl.textContent = 'Done.';
            })
            .catch(function (err) {
                if (statusEl) statusEl.textContent = 'Error: ' + err.message;
            });
        });
    })();
</script>

@endsection
