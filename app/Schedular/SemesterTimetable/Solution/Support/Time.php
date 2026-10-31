<?php

namespace App\Schedular\SemesterTimetable\Solution\Support;

final class Time
{
    public const DAY_END = 1440;

    public static function toMinutes(?string $time): ?int
    {
        if ($time === null || !preg_match('/^(\d{1,2}):(\d{2})/', $time, $matches)) {
            return null;
        }

        return ((int) $matches[1]) * 60 + (int) $matches[2];
    }

    public static function toTime(int $minutes): string
    {
        $minutes = max(0, min(self::DAY_END - 1, $minutes));

        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    public static function overlaps(int $aStart, int $aEnd, int $bStart, int $bEnd): bool
    {
        return $aStart < $bEnd && $aEnd > $bStart;
    }
}
