<?php

namespace App\Schedular\SemesterTimetable\Suggestion\DTO;

class SuggestionOptionDTO
{
    public function __construct(
        public string $action,
        public string $label,
        public ?array $meta = []
    ) {}
}
