<?php

namespace App\Schedular\ExamTimetable\Placement\Support;

class Time
{
    public static function toMinutes(string $time): int
    {
        [$hours, $minutes] = array_map('intval', array_pad(explode(':', $time), 2, 0));
        return $hours * 60 + $minutes;
    }

    public static function toTime(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    /** Normalises "8:00", "08:00:00" or a datetime string to "HH:MM". */
    public static function normalize(?string $time): ?string
    {
        if ($time === null || trim($time) === '') {
            return null;
        }
        if (preg_match('/(\d{1,2}):(\d{2})/', $time, $match)) {
            return sprintf('%02d:%02d', (int) $match[1], (int) $match[2]);
        }
        return null;
    }

    public static function overlaps(int $aStart, int $aEnd, int $bStart, int $bEnd): bool
    {
        return $aStart < $bEnd && $bStart < $aEnd;
    }

    /** Minutes between two intervals; 0 when they touch or overlap. */
    public static function gap(int $aStart, int $aEnd, int $bStart, int $bEnd): int
    {
        if ($aEnd <= $bStart) {
            return $bStart - $aEnd;
        }
        if ($bEnd <= $aStart) {
            return $aStart - $bEnd;
        }
        return 0;
    }

    public static function duration(string $start, string $end): int
    {
        return self::toMinutes($end) - self::toMinutes($start);
    }
}
