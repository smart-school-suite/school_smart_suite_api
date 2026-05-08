<?php

namespace App\Schedular\ExamTimetable\DTO;

class SessionSlotDTO
{
    public function __construct(
        public string $startTime,
        public string $endTime,
        public string $courseId,
        public array $candidateGroups = [],
    )
    {

    }
}
