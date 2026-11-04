<?php

namespace App\Schedular\ExamTimetable\DTO;

class ExamDateDTO
{
    /**
     * @param ExamSessionDTO[] $sessions
     */
    public function __construct(
        public string $date,
        public int $start,
        public int $end,
        public array $sessions = [],
    ) {}
}
