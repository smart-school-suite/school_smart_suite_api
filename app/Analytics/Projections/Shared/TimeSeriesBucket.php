<?php

namespace App\Analytics\Projections\Shared;

use Carbon\CarbonImmutable;

class TimeSeriesBucket
{
    public static function for(CarbonImmutable $date, string $granularity): string
    {
        return match ($granularity) {
            'hour'  => $date->format('Y-m-d H:00'), // e.g., 2025-12-20 08:00
            'day'   => $date->format('Y-m-d'),       // e.g., 2025-12-20
            'month' => $date->format('Y-m'),
            'year'  => $date->format('Y'),         // e.g., 2025-12
            default => throw new \InvalidArgumentException("Unknown granularity: $granularity"),
        };
    }
}
