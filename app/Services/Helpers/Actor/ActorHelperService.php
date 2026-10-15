<?php

namespace App\Services\Helpers\Actor;

use Illuminate\Support\Str;

class ActorHelperService
{
    public function generateUsername(
        string $name,
        string $model,
        string $column = 'username',
        array $additionalScopes = []
    ): string {
        $normalized = strtolower(trim($name));
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized) ?: $normalized;
        $normalized = preg_replace('/[^a-z0-9\s]/', '', $normalized);

        $parts = preg_split('/\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        $base = match (true) {
            empty($parts)       => 'user',
            count($parts) === 1 => $parts[0],
            default             => $parts[0][0] . end($parts),
        };

        if (strlen($base) < 3) {
            $base = str_pad($base, 3, '0');
        }

        $base = substr($base, 0, 20);

        $username = $base;
        $counter = 1;
        $query = $model::query();
        foreach ($additionalScopes as $scopeColumn => $scopeValue) {
            $query->where($scopeColumn, $scopeValue);
        }

        while ((clone $query)->where($column, $username)->exists()) {
            $suffix = (string) $counter++;
            $truncatedBase = substr($base, 0, 20 - strlen($suffix));
            $username = $truncatedBase . $suffix;
        }

        return $username;
    }

    public function generateRandomPassword(int $length = 10): string
    {
        return Str::random($length);
    }
}
