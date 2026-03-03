<?php

namespace App\Services\Marketing;

class MarketingHtmlFormatter
{
    public static function normalize(?string $html): string
    {
        $html = (string) ($html ?? '');

        if ($html === '') {
            return $html;
        }

        // Remove empty paragraphs that Quill inserts for spacing.
        $html = preg_replace('/<p>(?:\s|&nbsp;|<br\s*\/?>)*<\/p>/i', '', $html);

        // Ensure consistent paragraph spacing across email clients.
        $html = preg_replace_callback('/<p([^>]*)>/i', function ($matches) {
            $attrs = $matches[1] ?? '';
            if (stripos($attrs, 'style=') !== false) {
                return '<p' . $attrs . '>';
            }
            return '<p style="margin:0 0 10px 0;"' . $attrs . '>';
        }, $html);

        // Collapse excessive line breaks.
        $html = preg_replace('/(<br\s*\/?>\s*){3,}/i', '<br><br>', $html);

        return trim($html);
    }
}
