<?php

namespace App\Services\Import;

/**
 * Why a row could not be imported. The key is stored with each error group
 * so clients can style or filter by it; the title is the heading people see.
 */
final class ImportErrorReason
{
    public const ALREADY_EXISTS = 'already_exists';
    public const DUPLICATE_IN_FILE = 'duplicate_in_file';
    public const MISSING_VALUE = 'missing_value';
    public const INVALID_VALUE = 'invalid_value';
    public const NOT_FOUND = 'not_found';
    public const NOT_ALLOWED = 'not_allowed';
    public const UNEXPECTED = 'unexpected_error';

    private const TITLES = [
        self::ALREADY_EXISTS => 'Already exists',
        self::DUPLICATE_IN_FILE => 'Duplicate rows in the file',
        self::MISSING_VALUE => 'Missing values',
        self::INVALID_VALUE => 'Invalid values',
        self::NOT_FOUND => 'Not found',
        self::NOT_ALLOWED => 'Not allowed',
        self::UNEXPECTED => 'Unexpected error',
    ];

    public static function title(string $reason): string
    {
        return self::TITLES[$reason] ?? 'Could not be imported';
    }
}
