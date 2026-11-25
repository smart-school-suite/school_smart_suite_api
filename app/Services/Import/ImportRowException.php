<?php

namespace App\Services\Import;

use RuntimeException;

/**
 * Thrown by an import job when one row cannot be imported for a reason the
 * person who uploaded the file can fix. The row is skipped and reported; the
 * rest of the file carries on.
 *
 * Keep the message free of row-specific values (put those in $value) so rows
 * with the same problem are grouped together in the report.
 */
class ImportRowException extends RuntimeException
{
    public function __construct(
        public readonly string $reason,
        string $message,
        public readonly ?string $field = null,
        public readonly mixed $value = null,
    ) {
        parent::__construct($message);
    }

    public static function alreadyExists(string $message, ?string $field = null, mixed $value = null): self
    {
        return new self(ImportErrorReason::ALREADY_EXISTS, $message, $field, $value);
    }

    public static function notFound(string $message, ?string $field = null, mixed $value = null): self
    {
        return new self(ImportErrorReason::NOT_FOUND, $message, $field, $value);
    }

    public static function notAllowed(string $message, ?string $field = null, mixed $value = null): self
    {
        return new self(ImportErrorReason::NOT_ALLOWED, $message, $field, $value);
    }

    public static function missing(string $message, ?string $field = null): self
    {
        return new self(ImportErrorReason::MISSING_VALUE, $message, $field);
    }

    public static function invalid(string $message, ?string $field = null, mixed $value = null): self
    {
        return new self(ImportErrorReason::INVALID_VALUE, $message, $field, $value);
    }
}
