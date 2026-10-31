<?php

namespace App\Schedular\SemesterTimetable\Solution\Support;

final class Cost
{
    public const MOVE_REQUEST = 10;
    public const MOVE_OTHER_REQUEST = 20;
    public const CHANGE_DAY = 20;
    public const REPLACE_RESOURCE = 15;
    public const RELAX_LIMIT = 25;
    public const RELAX_PREFERENCE = 30;
    public const MOVE_BREAK = 25;
    public const EXTEND_OPERATIONAL_PERIOD = 30;
    public const CHANGE_PERIOD_DURATION = 35;
    public const REMOVE_REQUEST = 40;
    public const REMOVE_BREAK = 45;
    public const MOVE_JOINT_COURSE = 50;
    public const REMOVE_JOINT_COURSE = 80;
    public const MANUAL_REVIEW = 90;

    public static function shift(int $minutes, int $perUnit = 15): int
    {
        return intdiv(abs($minutes), $perUnit);
    }
}
