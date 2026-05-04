<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Solution\DTO;

class SolutionPathDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $scenarioId,
        public array $decision,
        public array $flow,
    ) {}
}
