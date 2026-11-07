<?php

namespace App\Schedular\ExamTimetable\Solution\Support;

final class Cost
{
    public const MOVE_REQUEST = 10;
    public const MOVE_OTHER_REQUEST = 20;
    public const CHANGE_DATE = 15;
    public const REPLACE_RESOURCE = 15;
    public const ADD_HALL = 15;
    public const RELAX_LIMIT = 25;
    public const CHANGE_DURATION = 30;
    public const EXTEND_OPENING_HOURS = 30;
    public const ALLOW_EXCLUDED_DATE = 35;
    public const FEWER_INVIGILATORS = 35;
    public const REMOVE_REQUEST = 40;
    public const MANUAL_REVIEW = 90;

    public static function shift(int $minutes, int $perUnit = 15): int
    {
        return intdiv(abs($minutes), $perUnit);
    }
}
