<?php

/**
 * Global SharpLync Helper Functions
 * Loaded automatically via Composer autoload.
 */

if (!function_exists('highlight')) {
    /**
     * Highlight search term inside a given text.
     *
     * @param string|null $text
     * @param string|null $term
     * @return string
     */
    function highlight($text, $term)
    {
        if (!$term || !$text) {
            return e($text);
        }

        // Escape text for HTML but allow highlighting safely
        $escapedText = e($text);

        // Build safe regex for the search term
        $pattern = '/' . preg_quote($term, '/') . '/i';

        // Wrap matches with yellow highlight span
        return preg_replace(
            $pattern,
            '<span style="background:yellow;padding:2px 3px;border-radius:3px;">$0</span>',
            $escapedText
        );
    }
}
