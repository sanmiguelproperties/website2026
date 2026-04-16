<?php

declare(strict_types=1);

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

final class RichTextSanitizer
{
    /**
     * @var array<int, string>
     */
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's',
        'ul', 'ol', 'li', 'blockquote',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'a', 'span', 'div', 'code', 'pre',
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private const ALLOWED_ATTRIBUTES = [
        '*' => ['class', 'style', 'title'],
        'a' => ['href', 'target', 'rel'],
    ];

    /**
     * @var array<int, string>
     */
    private const ALLOWED_STYLE_PROPERTIES = [
        'color',
        'background-color',
        'font-weight',
        'font-style',
        'font-size',
        'line-height',
        'letter-spacing',
        'text-align',
        'text-decoration',
        'margin',
        'margin-top',
        'margin-right',
        'margin-bottom',
        'margin-left',
        'padding',
        'padding-top',
        'padding-right',
        'padding-bottom',
        'padding-left',
        'border',
        'border-color',
        'border-radius',
    ];

    /**
     * @var array<int, string>
     */
    private const ALLOWED_REL_TOKENS = [
        'noopener',
        'noreferrer',
        'nofollow',
        'ugc',
        'sponsored',
    ];

    public static function sanitize(?string $html, ?string $fallback = null): string
    {
        $source = trim((string) $html);
        if ($source === '') {
            return self::escape((string) ($fallback ?? ''));
        }

        if ($source === strip_tags($source)) {
            return nl2br(self::escape($source), false);
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $encoded = mb_convert_encoding($source, 'HTML-ENTITIES', 'UTF-8');

        $previousLibxml = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="utf-8" ?><div id="__rich_text_root__">' . $encoded . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousLibxml);

        $root = $document->getElementsByTagName('div')->item(0);
        if (!$root instanceof DOMElement) {
            return self::escape($source);
        }

        self::sanitizeNode($root);

        $htmlOutput = trim(self::innerHtml($root));
        if ($htmlOutput === '') {
            return self::escape((string) ($fallback ?? ''));
        }

        return $htmlOutput;
    }

    private static function sanitizeNode(DOMNode $node): void
    {
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child->nodeType === XML_COMMENT_NODE) {
                $child->parentNode?->removeChild($child);
                continue;
            }

            if (!$child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                self::unwrapElement($child);
                continue;
            }

            self::sanitizeAttributes($child, $tag);
            self::sanitizeNode($child);
        }
    }

    private static function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        $allowed = array_merge(
            self::ALLOWED_ATTRIBUTES['*'],
            self::ALLOWED_ATTRIBUTES[$tag] ?? []
        );

        $attributes = [];
        foreach ($element->attributes as $attribute) {
            $attributes[] = $attribute->name;
        }

        foreach ($attributes as $name) {
            $lowerName = strtolower($name);
            $value = $element->getAttribute($name);

            if (str_starts_with($lowerName, 'on')) {
                $element->removeAttribute($name);
                continue;
            }

            if (!in_array($lowerName, $allowed, true)) {
                $element->removeAttribute($name);
                continue;
            }

            if ($lowerName === 'style') {
                $style = self::sanitizeStyle($value);
                if ($style === '') {
                    $element->removeAttribute($name);
                } else {
                    $element->setAttribute($name, $style);
                }
                continue;
            }

            if ($tag === 'a' && $lowerName === 'href') {
                $element->setAttribute($name, self::sanitizeUrl($value));
                continue;
            }

            if ($tag === 'a' && $lowerName === 'target') {
                if ($value !== '_blank' && $value !== '_self') {
                    $element->removeAttribute($name);
                }
                continue;
            }

            if ($tag === 'a' && $lowerName === 'rel') {
                $rel = self::sanitizeRel($value);
                if ($rel === '') {
                    $element->removeAttribute($name);
                } else {
                    $element->setAttribute($name, $rel);
                }
            }
        }

        if ($tag === 'a' && $element->getAttribute('target') === '_blank') {
            $tokens = preg_split('/\s+/', trim((string) $element->getAttribute('rel'))) ?: [];
            $normalized = [];
            foreach ($tokens as $token) {
                $token = strtolower(trim($token));
                if ($token !== '') {
                    $normalized[$token] = true;
                }
            }
            $normalized['noopener'] = true;
            $normalized['noreferrer'] = true;
            $element->setAttribute('rel', implode(' ', array_keys($normalized)));
        }
    }

    private static function sanitizeStyle(string $style): string
    {
        $entries = [];
        $allowed = array_flip(self::ALLOWED_STYLE_PROPERTIES);

        foreach (explode(';', $style) as $declaration) {
            if (!str_contains($declaration, ':')) {
                continue;
            }

            [$property, $value] = explode(':', $declaration, 2);
            $property = strtolower(trim($property));
            $value = trim($value);
            $valueLower = strtolower($value);

            if ($property === '' || $value === '' || !isset($allowed[$property])) {
                continue;
            }

            if (
                str_contains($valueLower, 'expression(')
                || str_contains($valueLower, 'javascript:')
                || str_contains($valueLower, 'vbscript:')
                || str_contains($valueLower, 'url(')
            ) {
                continue;
            }

            $cleanValue = trim(str_replace(['<', '>'], '', $value));
            if ($cleanValue === '') {
                continue;
            }

            $entries[] = "{$property}: {$cleanValue}";
        }

        return implode('; ', $entries);
    }

    private static function sanitizeUrl(string $url): string
    {
        $url = trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($url === '') {
            return '#';
        }

        if (
            str_starts_with($url, '#')
            || str_starts_with($url, '/')
            || str_starts_with($url, './')
            || str_starts_with($url, '../')
        ) {
            return $url;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (in_array($scheme, ['http', 'https', 'mailto', 'tel'], true)) {
            return $url;
        }

        return '#';
    }

    private static function sanitizeRel(string $rel): string
    {
        $allowed = array_flip(self::ALLOWED_REL_TOKENS);
        $tokens = preg_split('/\s+/', strtolower(trim($rel))) ?: [];

        $safe = [];
        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token === '' || !isset($allowed[$token])) {
                continue;
            }
            $safe[$token] = true;
        }

        return implode(' ', array_keys($safe));
    }

    private static function unwrapElement(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if (!$parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    private static function innerHtml(DOMElement $element): string
    {
        $output = '';
        foreach ($element->childNodes as $child) {
            $output .= $element->ownerDocument?->saveHTML($child) ?? '';
        }

        return $output;
    }

    private static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
