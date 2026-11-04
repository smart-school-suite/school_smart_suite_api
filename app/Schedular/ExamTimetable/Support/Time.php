<?php

namespace App\Schedular\ExamTimetable\Support;

use Carbon\Carbon;

final class Time
{
    public const DAY_END = 1440;

    public static function toMinutes(mixed $time): ?int
    {
        if ($time instanceof \DateTimeInterface) {
            return (int) $time->format('H') * 60 + (int) $time->format('i');
        }

        if (!is_string($time) || !preg_match('/(\d{1,2}):(\d{2})/', $time, $matches)) {
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

    public static function date(mixed $date): ?string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        if (!is_string($date) || trim($date) === '') {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
