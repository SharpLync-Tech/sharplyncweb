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

        // Tighten common signature blocks starting with "Regards".
        $html = preg_replace_callback(
            '/<p([^>]*)>\s*Regards,?\s*<\/p>\s*<p([^>]*)>(.*?)<\/p>\s*<p([^>]*)>(.*?)<\/p>/is',
            function ($matches) {
                $applyStyle = function (string $attrs, string $style): string {
                    if (stripos($attrs, 'style=') !== false) {
                        return preg_replace('/style=(["\'])(.*?)\\1/i', 'style="$2; ' . $style . '"', $attrs, 1);
                    }
                    return ' style="' . $style . '"' . $attrs;
                };

                $p1 = '<p' . $applyStyle($matches[1], 'margin:0 0 10px 0; line-height:1.6;') . '>Regards,</p>';
                $p2 = '<p' . $applyStyle($matches[2], 'margin:0 0 10px 0; line-height:1.6;') . '>' . $matches[3] . '</p>';
                $p3 = '<p' . $applyStyle($matches[4], 'margin:0 0 10px 0; line-height:1.6;') . '>' . $matches[5] . '</p>';

                return $p1 . $p2 . $p3;
            },
            $html,
            1
        );

        return trim($html);
    }
}
