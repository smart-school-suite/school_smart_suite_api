<?php

namespace App\Schedular\ExamTimetable\DTO;

class CandidateGroupDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $hallId,
        public array $invigilators = [],
        public int $candidateCount = 0
    )
    {
        //
    }
}
