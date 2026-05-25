<?php

namespace App\Schedular\ExamTimetable\DTO;

class ExamScheduleDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $date,
        public array $slots = []
    )
    {
        //
    }
}
