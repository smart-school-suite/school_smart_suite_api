<?php

namespace App\Services\RichText;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

/**
 * Keeps only the inline styles the desktop rich-text editor produces:
 * a text colour on <span> and a text alignment on paragraphs and headings.
 * Every other declaration (url(), expression(), positioning, etc.) is dropped.
 */
class RichTextStyleSanitizer implements AttributeSanitizerInterface
{
    private const ALIGNABLE = ['p', 'h1', 'h2', 'h3'];

    public function getSupportedElements(): ?array
    {
        return null;
    }

    public function getSupportedAttributes(): ?array
    {
        return ['style'];
    }

    public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
    {
        $kept = [];

        foreach (explode(';', $value) as $declaration) {
            if (!str_contains($declaration, ':')) {
                continue;
            }

            [$property, $propertyValue] = array_map('trim', explode(':', $declaration, 2));
            $property = strtolower($property);

            if ($property === 'color' && $element === 'span' && self::isSafeColor($propertyValue)) {
                $kept[] = 'color: ' . strtolower($propertyValue);
            } elseif (
                $property === 'text-align'
                && in_array($element, self::ALIGNABLE, true)
                && in_array(strtolower($propertyValue), ['left', 'right', 'center', 'justify'], true)
            ) {
                $kept[] = 'text-align: ' . strtolower($propertyValue);
            }
        }

        return $kept === [] ? null : implode('; ', $kept);
    }

    private static function isSafeColor(string $value): bool
    {
        return (bool) preg_match('/^#[0-9a-f]{3,8}$/i', $value)
            || (bool) preg_match('/^rgba?\(\s*\d{1,3}%?\s*,\s*\d{1,3}%?\s*,\s*\d{1,3}%?\s*(,\s*(0|1|0?\.\d+)\s*)?\)$/i', $value);
    }
}
