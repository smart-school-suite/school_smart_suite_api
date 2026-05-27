<?php

namespace App\Schedular\ExamTimetable\DTO;

class AllocationDTO
{
    public const TYPE_REGULAR = 'regular';
    public const TYPE_JOINT   = 'joint';
    public function __construct(
        public  string $type,
        public  string $start_time,
        public  string $end_time,
        public  array $hall,

    ) {
        //
    }
}
