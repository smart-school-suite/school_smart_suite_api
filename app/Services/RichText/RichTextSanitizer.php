<?php

namespace App\Services\RichText;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerAction;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Server-side sanitizer for HTML written in the desktop app's rich-text editor.
 *
 * HTML from the client is never trusted: everything is parsed and rebuilt
 * against an allowlist that matches what the editor can produce. Harmless
 * wrappers such as <div> are unwrapped (their text is kept); anything else
 * (<script>, <style>, <iframe>, forms, ...) is removed with its content.
 * Links and images may only use http(s) (and mailto for links), and inline
 * styles are reduced to text colour and alignment.
 */
class RichTextSanitizer
{
    /**
     * Upper bound on the raw input we are willing to parse, in bytes.
     */
    public const MAX_INPUT_LENGTH = 200_000;

    /**
     * Upper bound on the stored HTML, in characters. Description columns are
     * TEXT (65,535 bytes), so this leaves room for multi-byte characters.
     */
    public const MAX_STORED_LENGTH = 30_000;

    /**
     * Harmless wrappers the editor does not produce (e.g. HTML sent straight to
     * the API). They are unwrapped so their text survives; every other element
     * not explicitly allowed is dropped together with its content.
     */
    private const UNWRAPPED_ELEMENTS = [
        'div', 'section', 'article', 'header', 'footer', 'main', 'aside', 'nav', 'figure',
        'figcaption', 'address', 'center', 'font', 'small', 'big', 'sub', 'sup', 'mark',
        'abbr', 'cite', 'q', 'del', 'ins', 'kbd', 'samp', 'var', 'time', 'label', 'h4',
        'h5', 'h6', 'dl', 'dt', 'dd', 'table', 'caption', 'thead', 'tbody', 'tfoot', 'tr',
        'th', 'td', 'details', 'summary',
    ];

    private ?HtmlSanitizer $sanitizer = null;

    /**
     * Sanitize a rich-text value. Returns null when nothing visible is left
     * (e.g. "<p></p>" or a value that was only a script), so that "required"
     * rules still fail and empty optional values are stored as NULL.
     */
    public function sanitize(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $clean = trim($this->sanitizer()->sanitize($html));

        // An <img> whose src was rejected is left without one; remove it.
        $clean = preg_replace('/<img(?![^>]*\ssrc=)[^>]*>/i', '', $clean);

        return self::isBlank($clean) ? null : $clean;
    }

    /**
     * Visible text of a (sanitized or legacy plain-text) value.
     */
    public static function plainText(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $text = preg_replace('/<br\s*\/?>|<\/(p|h[1-6]|li|blockquote|pre)>/i', ' ', $html);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    public static function isBlank(?string $html): bool
    {
        return self::plainText($html) === '' && !preg_match('/<img\b/i', (string) $html);
    }

    private function sanitizer(): HtmlSanitizer
    {
        if ($this->sanitizer !== null) {
            return $this->sanitizer;
        }

        $config = (new HtmlSanitizerConfig())
            ->defaultAction(HtmlSanitizerAction::Drop)
            ->withMaxInputLength(self::MAX_INPUT_LENGTH)
            ->allowLinkSchemes(['https', 'http', 'mailto'])
            ->allowMediaSchemes(['https', 'http'])
            ->allowRelativeLinks(false)
            ->allowRelativeMedias(false)
            ->withAttributeSanitizer(new RichTextStyleSanitizer());

        foreach (['br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'code', 'pre', 'blockquote', 'ul', 'ol', 'li', 'hr'] as $element) {
            $config = $config->allowElement($element);
        }

        foreach (['p', 'h1', 'h2', 'h3', 'span'] as $element) {
            $config = $config->allowElement($element, ['style']);
        }

        $config = $config
            ->allowElement('a', ['href', 'title'])
            ->forceAttribute('a', 'target', '_blank')
            ->forceAttribute('a', 'rel', 'noopener noreferrer nofollow')
            ->allowElement('img', ['src', 'alt', 'title']);

        foreach (self::UNWRAPPED_ELEMENTS as $element) {
            $config = $config->blockElement($element);
        }

        return $this->sanitizer = new HtmlSanitizer($config);
    }
}
