<?php

namespace App\Schedular\SemesterTimetable\Suggestion\DTO;

class ScenarioDTO
{
    public function __construct(
        public string $id,
        public array $decision, // ['type' => 'keep', 'target_id' => ...]
        public array $resolutions // ResolutionDTO[]
    ) {}
}
