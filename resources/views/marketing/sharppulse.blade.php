<!-- Marketing Page: SharpPulse -->
@extends('layouts.base')

@section('title', 'SharpPulse')

@section('content')
<section class="fade-section" style="padding:60px 0;">
    <div style="max-width:900px;margin:0 auto;padding:0 20px;">
        <h1 style="margin-bottom:8px;color:#ffffff;">SharpPulse</h1>
        <p style="color:#ffffff;margin-bottom:28px;">
            Latest updates and announcements from SharpLync and SharpFleet.
        </p>

        @forelse($emails as $email)
            @php
                $anchor = 'email-' . $email->id;
                $pageUrl = url('/marketing/sharppulse') . '#' . $anchor;
                $shareText = urlencode($email->subject ?? 'SharpPulse Update');
                $shareUrl = urlencode($pageUrl);
            @endphp
            <article id="{{ $anchor }}" style="background:#fff;border-radius:16px;box-shadow:0 12px 30px rgba(10,42,77,0.08);padding:28px;margin-bottom:20px;">
                <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
                    <h2 style="margin:0;">{{ $email->subject }}</h2>
                    <span style="font-size:12px;color:#6b7a89;">
                        {{ $email->sent_at ? $email->sent_at->format('d/m/Y H:i') : '' }}
                    </span>
                </div>
                <div style="margin-top:14px;line-height:1.7;">
                    @php
                        $html = (string) ($email->body_html ?? '');
                        $patterns = [
                            '/^\\s*<p>\\s*Dear[^<]*<\\/p>\\s*/i',
                            '/^\\s*<p>\\s*Hi[^<]*<\\/p>\\s*/i',
                            '/^\\s*<p>\\s*Hello[^<]*<\\/p>\\s*/i',
                            '/^\\s*Dear[^<]*(<br\\s*\\/?>)?\\s*/i',
                        ];
                        foreach ($patterns as $pattern) {
                            $html = preg_replace($pattern, '', $html);
                        }
                    @endphp
                    {!! $html !!}
                </div>

                <div style="margin-top:16px;border-top:1px solid #eef2f6;padding-top:12px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                    <strong style="font-size:13px;color:#5b6b7a;">Share:</strong>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" style="color:#0ea5e9;text-decoration:none;">Facebook</a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" style="color:#0ea5e9;text-decoration:none;">LinkedIn</a>
                    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener" style="color:#0ea5e9;text-decoration:none;">X</a>
                </div>
            </article>
        @empty
            <div style="background:#fff;border-radius:16px;box-shadow:0 12px 30px rgba(10,42,77,0.08);padding:28px;">
                <p style="margin:0;color:#5b6b7a;">No updates published yet.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
