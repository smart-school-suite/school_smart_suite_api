<?php

namespace App\Schedular\SemesterTimetable\DTO;

class GridSlotDTO
{
    public const TYPE_REGULAR = 'regular';
    public const TYPE_BREAK   = 'break';
    public const TYPE_JOINT   = 'joint';
    public const TYPE_FREE  = 'free';
    public ?string $course_id  = null;
    public ?string $teacher_id = null;
    public ?string $hall_id    = null;

    public string $day;
    public string $start_time;
    public string $end_time;
    public string $type;

    public function __construct(
        string  $day        = '',
        string  $start_time = '',
        string  $end_time   = '',
        string  $type       = self::TYPE_REGULAR,
        ?string $course_id  = null,
        ?string $teacher_id = null,
        ?string $hall_id    = null,
    ) {
        $this->day        = $day;
        $this->start_time = $start_time;
        $this->end_time   = $end_time;
        $this->type       = $type;
        $this->course_id  = $course_id;
        $this->teacher_id = $teacher_id;
        $this->hall_id    = $hall_id;
    }

    public function isBreak(): bool
    {
        return $this->type === self::TYPE_BREAK;
    }

    public function isJoint(): bool
    {
        return $this->type === self::TYPE_JOINT;
    }

    public function isRegular(): bool
    {
        return $this->type === self::TYPE_REGULAR;
    }

    public function isFree(): bool
    {
        return $this->type === self::TYPE_FREE;
    }

}
