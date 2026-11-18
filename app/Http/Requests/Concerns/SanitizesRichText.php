<?php

namespace App\Http\Requests\Concerns;

use App\Services\RichText\RichTextSanitizer;
use Illuminate\Validation\ValidationException;

/**
 * Sanitizes rich-text (HTML) fields of a form request before it is validated,
 * so validation, validated() and input() only ever see clean HTML.
 *
 * Using requests list their fields in richTextFields(); nested fields use
 * wildcards, e.g. 'courses.*.description'. The default is 'description'.
 */
trait SanitizesRichText
{
    /**
     * @return list<string>
     */
    protected function richTextFields(): array
    {
        return ['description'];
    }

    public function validateResolved()
    {
        $this->sanitizeRichTextFields();

        parent::validateResolved();
    }

    protected function sanitizeRichTextFields(): void
    {
        $sanitizer = app(RichTextSanitizer::class);
        $errors = [];

        foreach ($this->richTextFields() as $path) {
            $segments = explode('.', $path);
            $root = array_shift($segments);

            if (!$this->has($root)) {
                continue;
            }

            $value = $this->sanitizeRichTextPath($this->input($root), $segments, $root, $sanitizer, $errors);
            $this->merge([$root => $value]);
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function sanitizeRichTextPath(mixed $value, array $segments, string $key, RichTextSanitizer $sanitizer, array &$errors): mixed
    {
        if ($segments === []) {
            if (!is_string($value)) {
                return $value;
            }

            if (strlen($value) > RichTextSanitizer::MAX_INPUT_LENGTH) {
                $errors[$key] = 'The ' . $this->richTextAttributeName($key) . ' is too long.';

                return null;
            }

            $clean = $sanitizer->sanitize($value);

            if ($clean !== null && mb_strlen($clean) > RichTextSanitizer::MAX_STORED_LENGTH) {
                $errors[$key] = 'The ' . $this->richTextAttributeName($key) . ' has too much formatting or content.';
            }

            return $clean;
        }

        if (!is_array($value)) {
            return $value;
        }

        $segment = array_shift($segments);

        foreach ($value as $index => $child) {
            if ($segment === '*' || (string) $index === $segment) {
                $value[$index] = $this->sanitizeRichTextPath($child, $segments, "{$key}.{$index}", $sanitizer, $errors);
            }
        }

        return $value;
    }

    private function richTextAttributeName(string $key): string
    {
        $last = substr($key, (int) strrpos($key, '.') + (str_contains($key, '.') ? 1 : 0));

        return str_replace('_', ' ', $last);
    }
}
