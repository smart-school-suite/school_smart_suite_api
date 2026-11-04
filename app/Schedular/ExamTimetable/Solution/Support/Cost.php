<?php

namespace App\Schedular\ExamTimetable\Solution\Support;

final class Cost
{
    public const MOVE_REQUEST = 10;
    public const MOVE_OTHER_REQUEST = 20;
    public const CHANGE_DATE = 15;
    public const REPLACE_RESOURCE = 15;
    public const LET_SCHEDULER_CHOOSE = 12;
    public const RELAX_LIMIT = 25;
    public const EXTEND_OPERATIONAL_PERIOD = 30;
    public const CHANGE_SESSION_DURATION = 35;
    public const REMOVE_REQUEST = 40;
    public const REOPEN_DATE = 45;
    public const MANUAL_REVIEW = 90;

    public static function shift(int $minutes, int $perUnit = 15): int
    {
        return intdiv(abs($minutes), $perUnit);
    }
}
