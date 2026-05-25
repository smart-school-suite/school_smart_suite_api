<?php

namespace App\Schedular\ExamTimetable\DTO;

class ExamDateDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $date,
        public string $startTime,
        public string $endTime,
        public array $allocations = []
    )
    {
        //
    }
}
