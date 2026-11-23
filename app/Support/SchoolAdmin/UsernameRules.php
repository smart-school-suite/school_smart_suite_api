<?php

namespace App\Support\SchoolAdmin;

use App\Models\Schooladmin;

class UsernameRules
{
    public const MIN_LENGTH = 3;

    public const MAX_LENGTH = 30;

    public static function normalize(?string $username): string
    {
        return strtolower(trim((string) $username));
    }

    public static function checks(?string $username, ?string $ignoreSchoolAdminId = null): array
    {
        $username = self::normalize($username);
        $length = strlen($username);

        $formatChecks = [
            [
                'key' => 'length',
                'label' => sprintf('%d to %d characters', self::MIN_LENGTH, self::MAX_LENGTH),
                'passed' => $length >= self::MIN_LENGTH && $length <= self::MAX_LENGTH,
            ],
            [
                'key' => 'characters',
                'label' => 'Only lowercase letters, numbers, dots and underscores',
                'passed' => $length > 0 && preg_match('/^[a-z0-9._]+$/', $username) === 1,
            ],
            [
                'key' => 'edges',
                'label' => 'Starts and ends with a letter or number',
                'passed' => preg_match('/^[a-z0-9](.*[a-z0-9])?$/', $username) === 1,
            ],
            [
                'key' => 'separators',
                'label' => 'No dots or underscores next to each other',
                'passed' => $length > 0 && preg_match('/[._]{2}/', $username) !== 1,
            ],
        ];

        $formatValid = collect($formatChecks)->every(fn (array $check) => $check['passed']);

        $available = $formatValid && !self::isTaken($username, $ignoreSchoolAdminId);

        $checks = [
            ...$formatChecks,
            [
                'key' => 'available',
                'label' => 'Not used by another account',
                'passed' => $available,
            ],
        ];

        return [
            'username' => $username,
            'valid' => $available,
            'checks' => $checks,
        ];
    }

    public static function isTaken(string $username, ?string $ignoreSchoolAdminId = null): bool
    {
        return Schooladmin::query()
            ->whereRaw('LOWER(username) = ?', [self::normalize($username)])
            ->when($ignoreSchoolAdminId, fn ($query) => $query->where('id', '!=', $ignoreSchoolAdminId))
            ->exists();
    }

    public static function failedMessage(array $result): ?string
    {
        $failed = collect($result['checks'])->firstWhere('passed', false);

        if (!$failed) {
            return null;
        }

        return $failed['key'] === 'available'
            ? 'This username is already taken.'
            : 'Username must follow this rule: ' . strtolower($failed['label']) . '.';
    }
}
