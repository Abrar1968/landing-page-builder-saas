<?php

namespace App\Services;

class HtmlSanitizer
{
    protected $purifier;

    public function __construct()
    {
        // HTMLPurifier configuration
        $config = \HTMLPurifier_Config::createDefault();

        // Allow safe HTML tags
        $config->set('HTML.Allowed', implode(',', [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
            'a[href|title|target]',
            'ul', 'ol', 'li',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'blockquote', 'code', 'pre',
            'img[src|alt|width|height|title]',
            'span[style]',
            'div[style]',
        ]));

        // Allow safe CSS properties for inline styles
        $config->set('CSS.AllowedProperties', implode(',', [
            'color',
            'background-color',
            'font-size',
            'font-weight',
            'font-family',
            'text-align',
            'text-decoration',
            'margin',
            'padding',
            'border',
            'border-radius',
            'width',
            'height',
            'display',
        ]));

        // Set encoding
        $config->set('Core.Encoding', 'UTF-8');

        // Cache directory for better performance
        $cacheDir = storage_path('app/htmlpurifier');
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        $config->set('Cache.SerializerPath', $cacheDir);

        // Allow target="_blank" for links
        $config->set('Attr.AllowedFrameTargets', ['_blank']);

        // Disable autoclose for better HTML5 compatibility
        $config->set('HTML.ForbiddenElements', ['script', 'style', 'iframe', 'object', 'embed']);

        $this->purifier = new \HTMLPurifier($config);
    }

    /**
     * Sanitize HTML content
     *
     * @param string $html
     * @return string
     */
    public function sanitize(string $html): string
    {
        if (empty($html)) {
            return '';
        }

        return $this->purifier->purify($html);
    }

    /**
     * Sanitize multiple HTML fields
     *
     * @param array $fields
     * @return array
     */
    public function sanitizeMultiple(array $fields): array
    {
        $sanitized = [];

        foreach ($fields as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->sanitize($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeMultiple($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Check if HTML contains potentially dangerous content
     *
     * @param string $html
     * @return bool
     */
    public function isDangerous(string $html): bool
    {
        $dangerousPatterns = [
            '/<script/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
            '/javascript:/i',
            '/on\w+\s*=/i', // onclick, onload, etc.
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $html)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Strip all HTML tags
     *
     * @param string $html
     * @return string
     */
    public function stripTags(string $html): string
    {
        return strip_tags($html);
    }

    /**
     * Sanitize for plain text output (strip tags but preserve line breaks)
     *
     * @param string $html
     * @return string
     */
    public function toPlainText(string $html): string
    {
        $text = str_replace(['<br>', '<br/>', '<br />'], "\n", $html);
        $text = str_replace('</p>', "\n\n", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        return trim($text);
    }
}
