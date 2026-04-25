<?php

namespace App\Schedular\SemesterTimetable\Suggestion\DTO;

class ScenarioDTO
{
    public function __construct(
        public string $id,
        public DecisionDTO $decision,
        public array $resolutions // ResolutionDTO[]
    ) {}
}
