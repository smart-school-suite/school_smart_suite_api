<?php

namespace App\Support\Storage;

use App\Exceptions\AppException;

class R2Config
{
    private const REQUIRED = [
        'key' => 'R2_ACCESS_KEY_ID',
        'secret' => 'R2_SECRET_ACCESS_KEY',
        'bucket' => 'R2_BUCKET',
        'endpoint' => 'R2_ENDPOINT',
    ];

    public static function missing(): array
    {
        $disk = config('filesystems.disks.r2', []);

        return collect(self::REQUIRED)
            ->filter(fn (string $variable, string $option) => blank($disk[$option] ?? null))
            ->values()
            ->all();
    }

    public static function ensureConfigured(): void
    {
        $missing = self::missing();

        if ($missing === []) {
            return;
        }

        throw new AppException(
            'R2 storage is not configured. Set ' . implode(', ', $missing)
                . ' in the API environment, then run php artisan config:clear (or config:cache).',
            500,
            'Storage Not Configured',
            'File uploads are not set up on the server yet. Please contact support.',
            null
        );
    }
}
