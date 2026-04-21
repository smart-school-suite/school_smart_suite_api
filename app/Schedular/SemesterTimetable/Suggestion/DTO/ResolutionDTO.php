<?php

namespace App\Schedular\SemesterTimetable\Suggestion\DTO;

class ResolutionDTO
{
    public function __construct(
        public string $type, // conflict | dependency
        public string $target_id,
        public string $target_type,
        public array $options // SuggestionOptionDTO[]
    ) {}
}
