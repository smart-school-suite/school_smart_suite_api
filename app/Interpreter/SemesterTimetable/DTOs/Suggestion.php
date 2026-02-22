<?php

namespace App\Interpreter\SemesterTimetable\DTOs;

class Suggestion
{
    public function __construct(
        public string $title,
        public string $description,
        public bool $actionable = true,
        public array $payload = []
    ) {}
}
