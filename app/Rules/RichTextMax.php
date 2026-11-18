<?php

namespace App\Rules;

use App\Services\RichText\RichTextSanitizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Length limit for rich-text fields, measured on the visible text rather than
 * the HTML markup, so formatting does not eat into the user's character budget.
 */
class RichTextMax implements ValidationRule
{
    public function __construct(private readonly int $max)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        if (mb_strlen(RichTextSanitizer::plainText($value)) > $this->max) {
            $fail("The :attribute must not be longer than {$this->max} characters.");
        }
    }
}
